<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileMoneyAccountResource extends JsonResource
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
            'user_id' => $this->user_id,
            'currency_id' => $this->currency_id,
            'provider' => $this->provider,
            'network_code' => $this->network_code,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'recipient_address' => $this->recipient_address,
            'recipient_email' => $this->recipient_email,
            'recipient_country' => $this->recipient_country,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at,
        ];
    }
}
