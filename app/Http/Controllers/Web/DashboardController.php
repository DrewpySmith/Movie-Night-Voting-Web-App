<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\RoomRepository;
use App\Services\InvitationService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected RoomRepository $roomRepository,
        protected InvitationService $invitationService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $myRooms = $user ? $this->roomRepository->getUserRooms($user->id, 10) : collect();
        $publicRooms = $this->roomRepository->getPublicRooms(10);
        $pendingInvitations = $user ? $this->invitationService->getPendingInvitations($user) : collect();

        return view('pages.dashboard', compact('myRooms', 'publicRooms', 'pendingInvitations'));
    }
}
