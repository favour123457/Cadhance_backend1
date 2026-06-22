<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $typeName = strtolower($this->subscription_plan->subscription_type->name ?? '');

        return [
            'id'                => $this->id,
            'subscription_plan' => new SubscriptionPlanResource($this->subscription_plan),
            'billing_cycle'     => $this->billing_cycle,
            'expire_at'         => $this->expire_at,
            'active'            => $this->active,
            'is_expired'        => $this->expire_at < now()->toDateString(),
            'badge'             => in_array($typeName, ['pro', 'firm']) ? $typeName : null,
            'created_at'        => $this->created_at,
        ];
    }
}
