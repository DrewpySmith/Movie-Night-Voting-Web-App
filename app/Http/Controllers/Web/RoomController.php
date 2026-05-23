<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MovieRoom;
use App\Services\InvitationService;
use App\Services\RoomService;
use App\Services\VotingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
        protected InvitationService $invitationService,
        protected VotingService $votingService,
    ) {}

    public function create(): View
    {
        return view('pages.rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['nullable', 'string', 'in:public,private'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $room = $this->roomService->createRoom($validated, $request->user());

        return redirect()->route('rooms.show', $room);
    }

    public function show(MovieRoom $room): View
    {
        $room->load(['host', 'members', 'movies' => function ($q) {
            $q->with(['votes', 'reactions']);
        }, 'winner']);

        return view('pages.rooms.show', compact('room'));
    }

    public function results(MovieRoom $room): View
    {
        $room->load(['host', 'members', 'movies', 'winner']);

        return view('pages.rooms.results', compact('room'));
    }

    public function edit(MovieRoom $room): View
    {
        $this->authorize('update', $room);

        return view('pages.rooms.edit', compact('room'));
    }

    public function update(Request $request, MovieRoom $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['nullable', 'string', 'in:public,private'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $room->update($validated);

        return redirect()->route('rooms.show', $room)
            ->with('status', 'Room updated!');
    }

    public function close(MovieRoom $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $this->roomService->closeRoom($room);

        return redirect()->route('rooms.show', $room)
            ->with('status', 'Room closed.');
    }

    public function regenerateCode(MovieRoom $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $this->roomService->regenerateInviteCode($room);

        return redirect()->route('rooms.show', $room)
            ->with('status', 'Invite code regenerated!');
    }

    public function invite(Request $request, MovieRoom $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate(['email' => 'required|email']);

        $this->invitationService->createInvitation($room, $request->user(), $validated['email']);

        return redirect()->route('rooms.show', $room)
            ->with('status', 'Invitation sent!');
    }

    public function transfer(Request $request, MovieRoom $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $newHost = \App\Models\User::findOrFail($validated['user_id']);

        if ($newHost->id === $room->host_id) {
            return redirect()->route('rooms.show', $room)
                ->withErrors(['transfer' => 'That user is already the host.']);
        }

        if (!$room->isMember($newHost)) {
            return redirect()->route('rooms.show', $room)
                ->withErrors(['transfer' => 'That user is not a member of this room.']);
        }

        $this->roomService->transferHost($room, $newHost);

        return redirect()->route('rooms.show', $room)
            ->with('status', "Host transferred to {$newHost->name}.");
    }

    public function leave(Request $request, MovieRoom $room): RedirectResponse
    {
        if ($room->isHost($request->user())) {
            return redirect()->route('rooms.show', $room)
                ->withErrors(['host' => 'Host cannot leave. Transfer ownership or close the room.']);
        }

        $this->roomService->leaveRoom($room, $request->user());

        return redirect()->route('dashboard')
            ->with('status', 'Left the room.');
    }
}
