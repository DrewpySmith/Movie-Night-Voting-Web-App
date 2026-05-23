<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MovieRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'host_id',
        'title',
        'description',
        'visibility',
        'invite_code',
        'scheduled_at',
        'status',
        'winner_movie_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MovieRoom $room) {
            if (empty($room->invite_code)) {
                $room->invite_code = Str::upper(Str::random(6));
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_members', 'room_id', 'user_id')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'room_movies', 'room_id', 'movie_id')
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

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'winner_movie_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function isHost(User $user): bool
    {
        return $this->host_id === $user->id;
    }

    public function hasMovie(Movie $movie): bool
    {
        return $this->movies()->where('movie_id', $movie->id)->exists();
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
