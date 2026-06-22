<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group' => new GroupResource($this->group),
            'subscription_date' => $this->subscription_date,
            'created_at' => $this->created_at,
        ];
    }
}
