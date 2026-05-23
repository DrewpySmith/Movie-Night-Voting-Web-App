<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => MovieRoom::factory(),
            'inviter_id' => User::factory(),
            'invitee_email' => fake()->safeEmail(),
            'token' => fake()->sha256(),
            'status' => InvitationStatus::Pending->value,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attrs) => [
            'expires_at' => now()->subDay(),
            'status' => InvitationStatus::Pending->value,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => InvitationStatus::Accepted->value,
            'invitee_id' => User::factory(),
        ]);
    }
}
