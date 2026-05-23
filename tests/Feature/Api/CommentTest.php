<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_get_comments(): void
    {
        $room = MovieRoom::factory()->create();

        $response = $this->getJson("/api/v1/rooms/{$room->id}/comments");

        $response->assertUnauthorized();
    }

    public function test_can_list_comments_for_room(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        Comment::factory()->count(3)->create(['room_id' => $room->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/rooms/{$room->id}/comments");

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", [
            'body' => 'Great movie!',
            'movie_id' => $movie->id,
        ]);

        $response->assertCreated();
        $response->assertJsonFragment(['body' => 'Great movie!']);
    }

    public function test_comment_requires_body(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", []);

        $response->assertStatus(422);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/rooms/{$room->id}/comments/{$comment->id}");

        $response->assertOk();
        $this->assertNotNull($comment->fresh()->deleted_at);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user1->id]);
        $room->members()->attach([$user1->id => ['role' => 'host', 'joined_at' => now()], $user2->id => ['role' => 'member', 'joined_at' => now()]]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user1->id]);

        $response = $this->actingAs($user2)->deleteJson("/api/v1/rooms/{$room->id}/comments/{$comment->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $comment = Comment::factory()->create(['room_id' => $room->id, 'user_id' => $user->id]);

        $response = $this->actingAs($admin)->deleteJson("/api/v1/rooms/{$room->id}/comments/{$comment->id}");

        $response->assertOk();
        $this->assertNotNull($comment->fresh()->deleted_at);
    }
}
