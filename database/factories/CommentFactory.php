<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\MovieRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => MovieRoom::factory(),
            'user_id' => User::factory(),
            'movie_id' => Movie::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
