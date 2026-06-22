<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'subscription_type' => new GeneralTypeTwoResource($this->subscription_type),
            'name'              => $this->name,
            'description'       => $this->description,
            'monthly_price'     => $this->monthly_price,
            'annual_price'      => $this->annual_price,
            'active'            => $this->active,
            'created_at'        => $this->created_at,
        ];
    }
}
