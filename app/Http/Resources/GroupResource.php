<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UsersResource($this->user),
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'link' => $this->link,
            'subscribers_count' => $this->subscribers_count,
            'platform' => new GeneralTypeResource($this->platform),
            'group_status' => new GeneralTypeResource($this->group_status),
            'created_at' => $this->created_at,
        ];
    }
}
