<?php

namespace Tests\Feature\Livewire;

use App\Livewire\VoteArea;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VoteAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cast_vote(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        Livewire::actingAs($user)
            ->test(VoteArea::class, ['room' => $room])
            ->call('vote', $movie->id, 'up')
            ->assertDispatched('vote-cast');
    }

    public function test_can_remove_vote(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $component = Livewire::actingAs($user)
            ->test(VoteArea::class, ['room' => $room]);

        $component->call('vote', $movie->id, 'up');
        $component->call('removeVote', $movie->id);

        $this->assertDatabaseMissing('movie_votes', [
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_can_calculate_winner(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $component = Livewire::actingAs($user)
            ->test(VoteArea::class, ['room' => $room]);

        $component->call('vote', $movie->id, 'up');
        $component->call('calculateWinner');

        $component->assertSet('winnerId', $movie->id);
        $this->assertEquals($movie->id, $room->fresh()->winner_movie_id);
    }
}
