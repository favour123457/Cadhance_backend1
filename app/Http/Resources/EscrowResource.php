<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EscrowResource extends JsonResource
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
            'customization_request' => new CustomizationRequestResource($this->customization_request),
            'amount' => $this->amount,
            'escrow_histories' => EscrowHistoryResource::collection($this->escrow_histories),
            'created_at' => $this->created_at,
        ];
    }
}
