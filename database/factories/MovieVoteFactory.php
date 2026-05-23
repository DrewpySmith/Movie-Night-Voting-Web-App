<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieVoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => MovieRoom::factory(),
            'movie_id' => Movie::factory(),
            'user_id' => User::factory(),
            'vote' => fake()->randomElement(['up', 'down']),
        ];
    }
}
