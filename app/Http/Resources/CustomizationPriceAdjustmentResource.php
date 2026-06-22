<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomizationPriceAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'reason' => $this->reason,
            'status' => $this->status,
            'is_final' => (bool) $this->is_final,
            'decision_reason' => $this->decision_reason,
            'requested_by_user_id' => $this->requested_by_user_id,
            'responded_by_user_id' => $this->responded_by_user_id,
            'responded_at' => $this->responded_at,
            'created_at' => $this->created_at,
        ];
    }
}
