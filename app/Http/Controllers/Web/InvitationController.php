<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService,
    ) {}

    public function accept(Request $request, string $token): RedirectResponse
    {
        if (!$request->user()) {
            return redirect()->route('login')->with('message', 'Log in to accept your invitation.');
        }

        $room = $this->invitationService->acceptInvitation($token, $request->user());

        if (!$room) {
            return redirect()->route('dashboard')->withErrors(['invitation' => 'Invalid or expired invitation.']);
        }

        return redirect()->route('rooms.show', $room)
            ->with('status', 'Invitation accepted! You have joined the room.');
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        $this->invitationService->declineInvitation($token);

        return redirect()->route('dashboard')
            ->with('status', 'Invitation declined.');
    }
}
