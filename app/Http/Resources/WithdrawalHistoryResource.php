<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalHistoryResource extends JsonResource
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
            'amount' => $this->amount,
            'reason' => $this->reason,
            'payment_method' => $this->payment_method,
            'status' => $this->status?->name,
            'status_id' => $this->withdrawal_status_id,
            'bank_account' => $this->bankAccount ? new BankAccountResource($this->bankAccount) : null,
            'mobile_money_account' => $this->mobileMoneyAccount ? new MobileMoneyAccountResource($this->mobileMoneyAccount) : null,
            'flutterwave_reference' => $this->flutterwave_reference,
            'failure_reason' => $this->failure_reason,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
