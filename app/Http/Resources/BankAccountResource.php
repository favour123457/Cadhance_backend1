<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
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
            'bank_id' => $this->bank_id,
            'currency_id' => $this->currency_id,
            'bank_name' => $this->bank_name,
            'bank_code' => $this->bank_code,
            'destination_branch_code' => $this->destination_branch_code,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
        ];
    }
}
