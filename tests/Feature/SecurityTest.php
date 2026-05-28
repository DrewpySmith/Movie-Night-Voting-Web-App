<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\MovieVote;
use App\Models\User;
use App\Services\OmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // ─── SQL Injection ───────────────────────────────────────────

    private array $sqlPayloads = [
        "' OR '1'='1",
        "'; DROP TABLE users; --",
        "1' UNION SELECT * FROM users --",
        "' OR 1=1 --",
        "admin'--",
        "' UNION SELECT null,null,null,null,null,null,null,null,null,null --",
        "1; SELECT * FROM personal_access_tokens",
        "')) OR 1=1 --",
    ];

    public function test_sql_injection_in_room_creation(): void
    {
        $user = User::factory()->create();

        foreach ($this->sqlPayloads as $payload) {
            $response = $this->actingAs($user)->postJson('/api/v1/rooms', [
                'title' => $payload,
                'description' => $payload,
                'visibility' => 'public',
            ]);
            $response->assertSuccessful();
            $this->assertDatabaseHas('movie_rooms', ['title' => $payload]);
        }
    }

    public function test_sql_injection_in_movie_search(): void
    {
        $user = User::factory()->create();

        $this->mock(OmdbService::class, function ($mock) {
            $mock->shouldReceive('search')->andReturn(['movies' => [], 'total' => 0]);
        });

        foreach ($this->sqlPayloads as $payload) {
            $response = $this->actingAs($user)->getJson('/api/v1/movies/search?q=' . urlencode($payload));
            $response->assertSuccessful();
        }
    }

    public function test_sql_injection_in_comment(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        foreach ($this->sqlPayloads as $payload) {
            $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", [
                'body' => $payload,
            ]);
            $response->assertSuccessful();
        }
    }

    public function test_sql_injection_in_login(): void
    {
        User::factory()->create(['email' => 'sqllogin@example.com']);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'sqllogin@example.com',
            'password' => "' OR '1'='1",
        ]);
        // Should fail auth (422), not crash
        $response->assertStatus(422);
    }

    public function test_sql_injection_in_registration(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => "'; DROP TABLE users; --",
            'email' => 'sqlreg' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSuccessful();
        $this->assertDatabaseHas('users', ['name' => "'; DROP TABLE users; --"]);
    }

    // ─── XSS (Cross-Site Scripting) ─────────────────────────────

    private array $xssPayloads = [
        '<script>alert("XSS")</script>',
        '<img src=x onerror=alert(1)>',
        '<svg onload=alert(1)>',
        '"><script>alert(document.cookie)</script>',
        "javascript:alert('XSS')",
        '<iframe src="javascript:alert(1)">',
        '{{7*7}}',
    ];

    public function test_xss_in_room_title(): void
    {
        $user = User::factory()->create();

        foreach ($this->xssPayloads as $payload) {
            $response = $this->actingAs($user)->postJson('/api/v1/rooms', [
                'title' => $payload,
                'visibility' => 'public',
            ]);
            $response->assertSuccessful();
            $this->assertDatabaseHas('movie_rooms', ['title' => $payload]);
        }
    }

    public function test_xss_in_comment_body(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        foreach ($this->xssPayloads as $payload) {
            $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", [
                'body' => $payload,
            ]);
            $response->assertSuccessful();
        }
    }

    public function test_xss_in_registration_name(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'xss' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSuccessful();
        $this->assertDatabaseHas('users', ['name' => '<script>alert("XSS")</script>']);
    }

    public function test_xss_reflected_in_search(): void
    {
        $user = User::factory()->create();

        $this->mock(OmdbService::class, function ($mock) {
            $mock->shouldReceive('search')->andReturn(['movies' => [], 'total' => 0]);
        });

        foreach ($this->xssPayloads as $payload) {
            $response = $this->actingAs($user)->getJson('/api/v1/movies/search?q=' . urlencode($payload));
            $response->assertSuccessful();
        }
    }

    // ─── CSRF Protection ────────────────────────────────────────

    public function test_csrf_protection_on_web_forms(): void
    {
        // Web routes should require CSRF token — posting without it should fail
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        // Without CSRF token, should get 419 (token mismatch) or redirect
        $this->assertContains($response->status(), [419, 302]);
    }

    // ─── Authentication Bypass ──────────────────────────────────

    public function test_unauthenticated_cannot_access_protected_routes(): void
    {
        $routes = [
            ['GET', '/api/v1/rooms'],
            ['POST', '/api/v1/rooms'],
            ['GET', '/api/v1/trending'],
        ];

        foreach ($routes as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertUnauthorized();
        }
    }

    public function test_unauthenticated_cannot_create_room(): void
    {
        $response = $this->postJson('/api/v1/rooms', [
            'title' => 'Hacked Room',
        ]);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_cannot_vote(): void
    {
        $room = MovieRoom::factory()->create();
        $movie = Movie::factory()->create();

        $response = $this->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'up',
        ]);
        $response->assertUnauthorized();
    }

    // ─── Authorization / IDOR ───────────────────────────────────

    public function test_non_member_cannot_vote(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'up',
        ]);
        $response->assertForbidden();
    }

    public function test_non_member_cannot_comment(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", [
            'body' => 'This should fail',
        ]);
        $response->assertForbidden();
    }

    public function test_non_member_cannot_suggest_movie(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach($host->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies", [
            'omdb_id' => $movie->omdb_id,
        ]);
        $response->assertForbidden();
    }

    public function test_non_member_cannot_view_private_room(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->getJson("/api/v1/rooms/{$room->id}");
        $response->assertForbidden();
    }

    public function test_non_host_cannot_declare_winner(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([
            $host->id => ['role' => 'host', 'joined_at' => now()],
            $member->id => ['role' => 'member', 'joined_at' => now()],
        ]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $host->id, 'status' => 'pending']);

        $response = $this->actingAs($member)->postJson("/api/v1/rooms/{$room->id}/declare-winner/{$movie->id}");
        $response->assertForbidden();
    }

    public function test_non_host_cannot_update_room(): void
    {
        $host = User::factory()->create();
        $member = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $host->id]);
        $room->members()->attach([
            $host->id => ['role' => 'host', 'joined_at' => now()],
            $member->id => ['role' => 'member', 'joined_at' => now()],
        ]);

        $response = $this->actingAs($member)->putJson("/api/v1/rooms/{$room->id}", [
            'title' => 'Hacked',
        ]);
        $response->assertForbidden();
    }

    // ─── Mass Assignment ────────────────────────────────────────

    public function test_cannot_mass_assign_is_admin_via_registration(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Hacker',
            'email' => 'hacker' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => true,
        ]);
        $response->assertSuccessful();

        $user = User::where('email', 'like', 'hacker%@example.com')->first();
        $this->assertFalse($user->is_admin);
    }

    // ─── Information Disclosure ─────────────────────────────────

    public function test_api_user_endpoint_does_not_expose_password_hash(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/user');
        $response->assertOk();
        $response->assertJsonMissing(['password', 'remember_token']);
    }

    public function test_404_does_not_disclose_application_structure(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-route-12345');
        $response->assertStatus(404);

        $content = $response->json();
        if (is_array($content)) {
            $this->assertArrayNotHasKey('trace', $content);
            $this->assertArrayNotHasKey('file', $content);
            $this->assertArrayNotHasKey('line', $content);
        }
    }

    public function test_debug_mode_does_not_leak_stack_traces(): void
    {
        $response = $this->getJson('/api/v1/rooms/999999999');
        // Unauthenticated should return 401, not 500
        $this->assertContains($response->status(), [401, 403, 404]);
    }

    // ─── Rate Limiting ──────────────────────────────────────────

    public function test_login_rate_limiting(): void
    {
        User::factory()->create(['email' => 'ratelimit@example.com']);

        // Attempt 6 logins (limit is 5)
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'ratelimit@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => 'ratelimit@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    // ─── Insecure Direct Object Reference (IDOR) ────────────────

    public function test_cannot_update_other_users_room_via_idor(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user1->id]);
        $room->members()->attach($user1->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($user2)->putJson("/api/v1/rooms/{$room->id}", [
            'title' => 'IDOR Attack',
        ]);
        $response->assertForbidden();
    }

    public function test_cannot_delete_other_users_comment_via_idor(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user1->id]);
        $room->members()->attach([
            $user1->id => ['role' => 'host', 'joined_at' => now()],
            $user2->id => ['role' => 'member', 'joined_at' => now()],
        ]);
        $comment = Comment::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user1->id,
        ]);

        $response = $this->actingAs($user2)->deleteJson("/api/v1/rooms/{$room->id}/comments/{$comment->id}");
        $response->assertForbidden();
    }

    // ─── Input Validation ───────────────────────────────────────

    public function test_room_title_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/rooms', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_comment_body_required(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/comments", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_vote_type_validation(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", [
            'vote' => 'invalid_type',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('vote');
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/movies/search?q=a');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('q');
    }

    // ─── Duplicate Action Prevention ─────────────────────────────

    public function test_cannot_vote_twice_on_same_movie(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();
        $room->movies()->attach($movie->id, ['suggested_by' => $user->id, 'status' => 'pending']);

        $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);
        $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies/{$movie->id}/vote", ['vote' => 'up']);

        $this->assertDatabaseCount('movie_votes', 1);
    }

    public function test_cannot_suggest_duplicate_movie(): void
    {
        $user = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user->id]);
        $room->members()->attach($user->id, ['role' => 'host', 'joined_at' => now()]);
        $movie = Movie::factory()->create();

        $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies", ['omdb_id' => $movie->omdb_id]);
        $response = $this->actingAs($user)->postJson("/api/v1/rooms/{$room->id}/movies", ['omdb_id' => $movie->omdb_id]);

        $response->assertStatus(409);
    }

    // ─── API Token Security ─────────────────────────────────────

    public function test_invalid_token_rejected(): void
    {
        $room = MovieRoom::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer invalid-token-12345')
            ->getJson("/api/v1/rooms/{$room->id}");
        $response->assertUnauthorized();
    }

    public function test_cannot_access_room_with别人的_token(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = MovieRoom::factory()->create(['host_id' => $user1->id]);
        $room->members()->attach($user1->id, ['role' => 'host', 'joined_at' => now()]);

        // user2 tries to view user1's private room
        $room2 = MovieRoom::factory()->create(['host_id' => $user2->id, 'visibility' => 'private']);
        $room2->members()->attach($user2->id, ['role' => 'host', 'joined_at' => now()]);

        $response = $this->actingAs($user1)->getJson("/api/v1/rooms/{$room2->id}");
        $response->assertForbidden();
    }
}
