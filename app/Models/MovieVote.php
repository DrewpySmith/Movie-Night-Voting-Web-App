<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'movie_id',
        'user_id',
        'vote',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(MovieRoom::class, 'room_id');
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
