<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'omdb_id' => 'tt' . fake()->unique()->numberBetween(1000000, 9999999),
            'title' => fake()->sentence(3),
            'year' => (string) fake()->year(),
            'genre' => fake()->word() . ', ' . fake()->word(),
            'plot' => fake()->paragraph(),
            'poster_url' => fake()->imageUrl(),
            'runtime' => fake()->numberBetween(80, 180) . ' min',
            'imdb_rating' => (string) fake()->randomFloat(1, 1, 10),
            'actors' => fake()->name() . ', ' . fake()->name(),
            'director' => fake()->name(),
            'cached_at' => now(),
        ];
    }
}
