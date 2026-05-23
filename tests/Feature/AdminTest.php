<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_admin_can_view_users_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
    }

    public function test_admin_can_view_rooms_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/rooms');

        $response->assertOk();
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete('/admin/users/' . $user->id);

        $response->assertRedirect();
        $this->assertModelMissing($user);
    }

    public function test_admin_can_delete_room(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $room = \App\Models\MovieRoom::factory()->create();

        $response = $this->actingAs($admin)->delete('/admin/rooms/' . $room->id);

        $response->assertRedirect();
        $this->assertNotNull($room->fresh()->deleted_at);
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}
