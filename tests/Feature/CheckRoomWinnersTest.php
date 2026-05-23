<?php

namespace Tests\Feature;

use App\Console\Commands\CheckRoomWinners;
use App\Enums\RoomStatus;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRoomWinnersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_closes_expired_room_with_winner(): void
    {
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create([
            'host_id' => $host->id,
            'scheduled_at' => now()->subHour(),
            'status' => RoomStatus::Open->value,
        ]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'approved']);

        MovieVote::factory()->create([
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'user_id' => $host->id,
            'vote' => 'up',
        ]);

        $this->artisan(CheckRoomWinners::class)
            ->expectsOutputToContain($movie->title)
            ->assertSuccessful();

        $room->refresh();
        $this->assertEquals(RoomStatus::Closed->value, $room->status);
        $this->assertEquals($movie->id, $room->winner_movie_id);
    }

    public function test_command_closes_expired_room_without_winner(): void
    {
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create([
            'host_id' => $host->id,
            'scheduled_at' => now()->subHour(),
            'status' => RoomStatus::Open->value,
        ]);

        $this->artisan(CheckRoomWinners::class)
            ->expectsOutputToContain('no winner')
            ->assertSuccessful();

        $room->refresh();
        $this->assertEquals(RoomStatus::Closed->value, $room->status);
        $this->assertNull($room->winner_movie_id);
    }

    public function test_command_skips_future_rooms(): void
    {
        $host = User::factory()->create();
        MovieRoom::factory()->create([
            'host_id' => $host->id,
            'scheduled_at' => now()->addDay(),
            'status' => RoomStatus::Open->value,
        ]);

        $this->artisan(CheckRoomWinners::class)
            ->expectsOutput('No rooms to process.')
            ->assertSuccessful();
    }

    public function test_command_skips_already_closed_rooms(): void
    {
        $host = User::factory()->create();
        MovieRoom::factory()->create([
            'host_id' => $host->id,
            'scheduled_at' => now()->subHour(),
            'status' => RoomStatus::Closed->value,
        ]);

        $this->artisan(CheckRoomWinners::class)
            ->expectsOutput('No rooms to process.')
            ->assertSuccessful();
    }

    public function test_command_skips_rooms_without_scheduled_at(): void
    {
        $host = User::factory()->create();
        MovieRoom::factory()->create([
            'host_id' => $host->id,
            'scheduled_at' => null,
            'status' => RoomStatus::Open->value,
        ]);

        $this->artisan(CheckRoomWinners::class)
            ->expectsOutput('No rooms to process.')
            ->assertSuccessful();
    }
}
