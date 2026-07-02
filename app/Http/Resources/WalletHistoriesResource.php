<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletHistoriesResource extends JsonResource
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
            'wallet' => new WalletResource($this->wallet),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'amount_usd' => $this->amount_usd,
            'wallet_history_type' => new GeneralTypeTwoResource($this->wallet_history_type),
            'wallet_history_status' => new GeneralTypeTwoResource($this->wallet_history_status),
            'created_at' => $this->created_at,
        ];
    }
}
