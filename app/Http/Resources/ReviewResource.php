<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user'            => new MiniUserResource($this->user),
            'reviewable_type' => $this->reviewable_type,
            'reviewable_id'   => $this->reviewable_id,
            'rating'          => $this->rating,
            'comment'         => $this->comment,
            'created_at'      => $this->created_at,
        ];
    }
}
