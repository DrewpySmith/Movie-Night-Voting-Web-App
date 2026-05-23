<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieRoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'visibility' => 'public',
            'invite_code' => Str::upper(Str::random(6)),
            'status' => 'open',
        ];
    }

    public function private(): static
    {
        return $this->state(fn(array $attributes) => [
            'visibility' => 'private',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
