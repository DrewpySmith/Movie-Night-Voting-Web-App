<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'movie_id' => $this->movie_id,
            'user_id' => $this->user_id,
            'vote' => $this->vote,
            'created_at' => $this->created_at,
        ];
    }
}
