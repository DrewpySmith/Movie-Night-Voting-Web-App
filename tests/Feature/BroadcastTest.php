<?php

namespace Tests\Feature;

use App\Events\MovieSuggested;
use App\Events\RoomUpdated;
use App\Events\VoteCast;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_vote_cast_event_broadcasts_on_room_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $vote = MovieVote::factory()->create([
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $user->id,
            'vote' => 'up',
        ]);

        VoteCast::dispatch($room, $movie, $vote);

        Event::assertDispatched(VoteCast::class, function ($event) use ($room) {
            return $event->broadcastOn()->name === 'private-room.' . $room->id;
        });
    }

    public function test_movie_suggested_event_broadcasts_on_room_channel(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $movie = Movie::factory()->create();

        MovieSuggested::dispatch($room, $movie, $user);

        Event::assertDispatched(MovieSuggested::class, function ($event) use ($room) {
            return $event->broadcastOn()->name === 'private-room.' . $room->id;
        });
    }

    public function test_room_updated_event_broadcasts_on_room_channel(): void
    {
        Event::fake();

        $room = MovieRoom::factory()->create();

        RoomUpdated::dispatch($room, 'closed');

        Event::assertDispatched(RoomUpdated::class, function ($event) use ($room) {
            return $event->broadcastOn()->name === 'private-room.' . $room->id;
        });
    }
}
