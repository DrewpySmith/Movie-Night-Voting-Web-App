<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'omdb_id',
        'tmdb_id',
        'title',
        'year',
        'genre',
        'plot',
        'poster_url',
        'runtime',
        'imdb_rating',
        'actors',
        'director',
        'trailer_url',
        'cached_at',
    ];

    protected function casts(): array
    {
        return [
            'cached_at' => 'datetime',
        ];
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(MovieRoom::class, 'room_movies', 'movie_id', 'room_id')
            ->withPivot('suggested_by', 'status')
            ->withTimestamps();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(MovieVote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function upvotesCount(): int
    {
        return $this->votes()->where('vote', 'up')->count();
    }

    public function downvotesCount(): int
    {
        return $this->votes()->where('vote', 'down')->count();
    }

    public function score(): int
    {
        return $this->upvotesCount() - $this->downvotesCount();
    }
}
