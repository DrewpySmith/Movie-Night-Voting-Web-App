<?php

namespace Tests\Feature\Livewire;

use App\Livewire\RoomChat;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_submit_comment(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();

        Livewire::actingAs($user)
            ->test(RoomChat::class, ['room' => $room, 'movieId' => $movie->id])
            ->set('body', 'Great movie!')
            ->call('submit')
            ->assertDispatched('comment-added')
            ->assertSet('body', '');

        $this->assertDatabaseHas('comments', [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'body' => 'Great movie!',
        ]);
    }

    public function test_comment_requires_body(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        Livewire::actingAs($user)
            ->test(RoomChat::class, ['room' => $room])
            ->set('body', '')
            ->call('submit')
            ->assertHasErrors('body');
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(RoomChat::class, ['room' => $room])
            ->call('delete', $comment->id)
            ->assertDispatched('comment-deleted');

        $this->assertNotNull($comment->fresh()->deleted_at);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user1->id]);
        $room->members()->attach([$user1->id => ['role' => 'host', 'joined_at' => now()], $user2->id => ['role' => 'member', 'joined_at' => now()]]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user1->id]);

        Livewire::actingAs($user2)
            ->test(RoomChat::class, ['room' => $room])
            ->call('delete', $comment->id);

        $this->assertModelExists($comment);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user->id]);

        Livewire::actingAs($admin)
            ->test(RoomChat::class, ['room' => $room])
            ->call('delete', $comment->id)
            ->assertDispatched('comment-deleted');

        $this->assertNotNull($comment->fresh()->deleted_at);
    }
}
