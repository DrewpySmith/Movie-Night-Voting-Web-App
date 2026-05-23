<?php

namespace Tests\Unit;

use App\Enums\VoteType;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_cast_vote(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movie = Movie::factory()->create();

        $service = app(VotingService::class);
        $vote = $service->castVote($room, $movie, $user, VoteType::Up);

        $this->assertDatabaseHas('movie_votes', [
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $user->id,
            'vote' => 'up',
        ]);
    }

    public function test_user_can_change_vote(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movie = Movie::factory()->create();

        $service = app(VotingService::class);
        $service->castVote($room, $movie, $user, VoteType::Up);
        $service->castVote($room, $movie, $user, VoteType::Down);

        $this->assertEquals('down', MovieVote::where([
            'room_id' => $room->id, 'movie_id' => $movie->id, 'user_id' => $user->id,
        ])->first()->vote);
    }

    public function test_calculates_winner_by_highest_score(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $movieA = Movie::factory()->create(['title' => 'Movie A']);
        $movieB = Movie::factory()->create(['title' => 'Movie B']);

        $service = app(VotingService::class);
        $service->castVote($room, $movieA, $user1, VoteType::Up);
        $service->castVote($room, $movieA, $user2, VoteType::Up);
        $service->castVote($room, $movieB, $user1, VoteType::Up);
        $service->castVote($room, $movieB, $user2, VoteType::Down);

        $winner = $service->calculateWinner($room);

        $this->assertNotNull($winner);
        $this->assertEquals('Movie A', $winner->title);
    }
}
