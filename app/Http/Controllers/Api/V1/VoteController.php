<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationType;
use App\Enums\VoteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoteRequest;
use App\Http\Resources\VoteResource;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Notifications\RoomNotification;
use App\Services\VotingService;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    public function __construct(
        protected VotingService $votingService,
    ) {}

    public function store(StoreVoteRequest $request, MovieRoom $room, Movie $movie): VoteResource
    {
        $vote = $this->votingService->castVote(
            $room,
            $movie,
            $request->user(),
            VoteType::from($request->vote)
        );

        return VoteResource::make($vote);
    }

    public function destroy(MovieRoom $room, Movie $movie): JsonResponse
    {
        $this->votingService->removeVote($room, $movie, request()->user());

        return response()->json(['message' => 'Vote removed']);
    }

    public function index(MovieRoom $room): JsonResponse
    {
        $tally = $this->votingService->getVoteTally($room);

        return response()->json(['tally' => $tally]);
    }

    public function winner(MovieRoom $room): JsonResponse
    {
        $winner = $this->votingService->calculateWinner($room);

        if (!$winner) {
            return response()->json(['message' => 'No winner yet'], 404);
        }

        return response()->json([
            'winner' => [
                'id' => $winner->id,
                'title' => $winner->title,
                'poster_url' => $winner->poster_url,
            ],
        ]);
    }

    public function declareWinner(MovieRoom $room, Movie $movie): JsonResponse
    {
        $this->authorize('update', $room);

        $this->votingService->declareWinner($room, $movie);

        $room->members->each(function ($member) use ($room, $movie) {
            if ($member->id !== request()->user()->id) {
                $member->notify(new RoomNotification(
                    NotificationType::WinnerDeclared,
                    [
                        'message' => $movie->title . ' won in ' . $room->title,
                        'room_id' => $room->id,
                        'movie_title' => $movie->title,
                        'room_title' => $room->title,
                        'action_url' => '/rooms/' . $room->id,
                    ]
                ));
            }
        });

        return response()->json(['message' => 'Winner declared', 'movie_id' => $movie->id]);
    }
}
