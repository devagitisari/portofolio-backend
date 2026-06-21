<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resolveImageUrl = fn(?string $image): ?string => $image
            ? (str_starts_with($image, 'http') ? $image : asset('storage/' . $image))
            : null;
        $firstGalleryImage = optional($this->images->first())->image;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'longDescription' => $this->long_description,
            'projectRole' => $this->project_role,
            'problem' => $this->problem,
            'solution' => $this->solution,
            'keyFeatures' => $this->key_features ?? [],
            'impact' => $this->impact,
            'category' => $this->category,
            'status' => $this->status,
            'image' => $resolveImageUrl($this->thumbnail) ?? $resolveImageUrl($firstGalleryImage),
            'skillIds' => $this->whenLoaded('skills', fn() => $this->skills->pluck('id')->map(fn($id) => (string) $id)->values()->all(), []),
            'skillNames' => $this->whenLoaded('skills', fn() => $this->skills->pluck('name')->values()->all(), []),
            'demoUrl' => $this->demo_url,
            'githubUrl' => $this->github_url,
            'featured' => $this->featured,
            'startDate' => optional($this->start_date)->format('Y-m-d'),
            'endDate' => optional($this->end_date)->format('Y-m-d'),
            'images' => ProjectImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
