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
            'recipient_email' => $this->recipient_email,
            'recipient_address' => $this->recipient_address,
            'recipient_city' => $this->recipient_city,
            'recipient_country' => $this->recipient_country,
            'recipient_phone' => $this->recipient_phone,
            'account_type' => $this->account_type,
            'routing_number' => $this->routing_number,
            'swift_code' => $this->swift_code,
            'postal_code' => $this->postal_code,
            'bank_branch' => $this->bank_branch,
            'beneficiary_country' => $this->beneficiary_country,
            'sender_id_type' => $this->sender_id_type,
            'sender_id_number' => $this->sender_id_number,
            'transfer_purpose_code' => $this->transfer_purpose_code,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
        ];
    }
}
