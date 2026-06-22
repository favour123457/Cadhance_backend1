<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'design_category' => new GeneralTypeTwoResource($this->design_category),
            'design_type' => new GeneralTypeTwoResource($this->design_type),
            'description' => $this->description,
            'duration' => $this->duration,
            'tools_used' => $this->tools_used,
            'media' => UserPortfolioMediaResource::collection($this->media),
            'created_at' => $this->created_at,
        ];
    }
}
