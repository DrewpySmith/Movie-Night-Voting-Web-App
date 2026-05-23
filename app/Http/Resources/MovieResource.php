<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'omdb_id' => $this->omdb_id,
            'title' => $this->title,
            'year' => $this->year,
            'genre' => $this->genre,
            'plot' => $this->plot,
            'poster_url' => $this->poster_url,
            'runtime' => $this->runtime,
            'imdb_rating' => $this->imdb_rating,
            'actors' => $this->actors,
            'director' => $this->director,
            'score' => $this->whenHas('score', fn() => $this->score),
            'pivot' => $this->when($this->pivot, fn() => [
                'status' => $this->pivot?->status,
                'suggested_by' => $this->pivot?->suggested_by,
            ]),
        ];
    }
}
