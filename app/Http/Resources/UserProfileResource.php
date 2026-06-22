<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'bio' => $this->bio,
            'visibility' => $this->visibility,
            'design_category' => new GeneralTypeTwoResource($this->design_category),
            'social_links' => $this->social_links,
            'primary_role' => new GeneralTypeTwoResource($this->primary_role),
            'studio_name' => $this->studio_name,
            'is_studio_name_display_name' => $this->is_studio_name_display_name,
        ];
    }
}
