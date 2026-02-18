<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\LevelResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            // menampilkan detail level jika sudah selesai load (eager-load)
            'level' => new LevelResource($this->whenLoaded('level')),
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail,
            'type' => $this->type,
            'price' => $this->price,
            'is_published' => $this->is_published,
        ];
    }
}
