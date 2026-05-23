<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteUserRequest;
use App\Http\Resources\RoomResource;
use App\Models\MovieRoom;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService,
    ) {}

    public function invite(InviteUserRequest $request, MovieRoom $room): JsonResponse
    {
        $this->authorize('update', $room);

        $invitation = $this->invitationService->createInvitation(
            $room,
            $request->user(),
            $request->email,
        );

        return response()->json([
            'message' => 'Invitation sent',
            'token' => $invitation->token,
        ]);
    }

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $invitations = $this->invitationService->getPendingInvitations($request->user());

        return \App\Http\Resources\RoomResource::collection($invitations->pluck('room'));
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $room = $this->invitationService->acceptInvitation($token, $request->user());

        if (!$room) {
            return response()->json(['message' => 'Invalid or expired invitation'], 404);
        }

        return response()->json([
            'message' => 'Invitation accepted',
            'room' => RoomResource::make($room->load('host')),
        ]);
    }

    public function decline(string $token): JsonResponse
    {
        $this->invitationService->declineInvitation($token);

        return response()->json(['message' => 'Invitation declined']);
    }
}
