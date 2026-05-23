<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'visibility' => $this->visibility,
            'invite_code' => $this->when($request->user()?->id === $this->host_id, $this->invite_code),
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'host' => UserResource::make($this->whenLoaded('host')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'movies' => MovieResource::collection($this->whenLoaded('movies')),
            'winner' => MovieResource::make($this->whenLoaded('winner')),
            'member_count' => $this->whenCounted('members'),
            'movie_count' => $this->whenCounted('movies'),
            'created_at' => $this->created_at,
        ];
    }
}
