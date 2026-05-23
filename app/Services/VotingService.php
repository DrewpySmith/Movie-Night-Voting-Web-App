<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\RoomStatus;
use App\Enums\VoteType;
use App\Events\VoteCast;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use App\Notifications\RoomNotification;
use Illuminate\Support\Facades\DB;

class VotingService
{
    public function castVote(MovieRoom $room, Movie $movie, User $user, VoteType $voteType): MovieVote
    {
        $vote = MovieVote::updateOrCreate(
            [
                'room_id' => $room->id,
                'movie_id' => $movie->id,
                'user_id' => $user->id,
            ],
            ['vote' => $voteType->value]
        );

        broadcast(new VoteCast($room, $movie, $vote))->toOthers();

        if ($room->host_id !== $user->id) {
            $room->host->notify(new RoomNotification(
                NotificationType::VoteReceived,
                [
                    'message' => $vote->vote === 'up'
                        ? $user->name . ' upvoted ' . $movie->title
                        : $user->name . ' downvoted ' . $movie->title,
                    'room_id' => $room->id,
                    'actor_name' => $user->name,
                    'movie_title' => $movie->title,
                    'vote_type' => $vote->vote,
                    'action_url' => '/rooms/' . $room->id,
                ]
            ));
        }

        return $vote;
    }

    public function removeVote(MovieRoom $room, Movie $movie, User $user): void
    {
        MovieVote::where([
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $user->id,
        ])->delete();
    }

    public function calculateWinner(MovieRoom $room): ?Movie
    {
        $scores = MovieVote::where('room_id', $room->id)
            ->select('movie_id', DB::raw('SUM(CASE WHEN vote = "up" THEN 1 WHEN vote = "down" THEN -1 ELSE 0 END) as score'))
            ->groupBy('movie_id')
            ->orderByDesc('score')
            ->orderBy('created_at')
            ->get();

        if ($scores->isEmpty() || $scores->first()->score <= 0) {
            return null;
        }

        $winner = Movie::find($scores->first()->movie_id);

        if ($winner) {
            $room->update([
                'winner_movie_id' => $winner->id,
                'status' => RoomStatus::Closed->value,
            ]);
        }

        return $winner;
    }

    public function declareWinner(MovieRoom $room, Movie $movie): Movie
    {
        $room->update([
            'winner_movie_id' => $movie->id,
            'status' => RoomStatus::Closed->value,
        ]);

        return $movie;
    }

    public function getVoteTally(MovieRoom $room): array
    {
        return MovieVote::where('room_id', $room->id)
            ->select('movie_id', DB::raw('SUM(CASE WHEN vote = "up" THEN 1 ELSE 0 END) as upvotes'),
                DB::raw('SUM(CASE WHEN vote = "down" THEN 1 ELSE 0 END) as downvotes'),
                DB::raw('SUM(CASE WHEN vote = "up" THEN 1 WHEN vote = "down" THEN -1 ELSE 0 END) as score'))
            ->groupBy('movie_id')
            ->orderByDesc('score')
            ->orderBy('created_at')
            ->get()
            ->keyBy('movie_id')
            ->toArray();
    }
}
