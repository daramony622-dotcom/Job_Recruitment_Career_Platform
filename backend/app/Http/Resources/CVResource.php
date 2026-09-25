<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CVResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fileUrl = null;
        if (!empty($this->file_path)) {
            if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
                $fileUrl = $this->file_path;
            } else {
                $clean = ltrim($this->file_path, '/');
                if (str_starts_with($clean, 'storage/')) {
                    $fileUrl = asset($clean);
                } else {
                    $fileUrl = asset('storage/' . $clean);
                }
            }
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'file_path' => $fileUrl,
            'is_primary' => (bool) $this->is_primary,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}