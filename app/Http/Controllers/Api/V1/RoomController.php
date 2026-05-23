<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\MovieRoom;
use App\Repositories\RoomRepository;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function __construct(
        protected RoomService $roomService,
        protected RoomRepository $roomRepository,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $rooms = $request->user()
            ? $this->roomRepository->getUserRooms($request->user()->id)
            : $this->roomRepository->getPublicRooms();

        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request): RoomResource
    {
        $room = $this->roomService->createRoom($request->validated(), $request->user());

        return RoomResource::make($room->load(['host', 'members']));
    }

    public function show(MovieRoom $room): RoomResource
    {
        $room->load(['host', 'members', 'movies', 'winner', 'votes']);
        $room->loadCount('members', 'movies');

        return RoomResource::make($room);
    }

    public function update(StoreRoomRequest $request, MovieRoom $room): RoomResource
    {
        $this->authorize('update', $room);

        $room->update($request->validated());

        return RoomResource::make($room->load('host'));
    }

    public function destroy(MovieRoom $room): JsonResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }

    public function join(Request $request, MovieRoom $room): JsonResponse
    {
        if ($room->isMember($request->user())) {
            return response()->json(['message' => 'Already a member'], 409);
        }

        $this->roomService->joinRoom($room, $request->user());

        return response()->json(['message' => 'Joined room']);
    }

    public function leave(Request $request, MovieRoom $room): JsonResponse
    {
        if (!$room->isMember($request->user())) {
            return response()->json(['message' => 'Not a member'], 404);
        }

        $this->roomService->leaveRoom($room, $request->user());

        return response()->json(['message' => 'Left room']);
    }

    public function joinByCode(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $room = $this->roomService->joinByCode($request->code, $request->user());

        if (!$room) {
            return response()->json(['message' => 'Invalid or expired invite code'], 404);
        }

        return response()->json([
            'message' => 'Joined room',
            'room' => RoomResource::make($room->load('host')),
        ]);
    }

    public function close(MovieRoom $room): JsonResponse
    {
        $this->authorize('update', $room);

        $this->roomService->closeRoom($room);

        return response()->json(['message' => 'Room closed']);
    }

    public function regenerateCode(MovieRoom $room): JsonResponse
    {
        $this->authorize('update', $room);

        $code = $this->roomService->regenerateInviteCode($room);

        return response()->json(['invite_code' => $code]);
    }
}
