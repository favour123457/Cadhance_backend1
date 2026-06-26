<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    public const PENDING    = 1;
    public const PROCESSING = 2;
    public const COMPLETED  = 3;
    public const FAILED     = 4;

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'mobile_money_account_id',
        'payment_method',
        'reason',
        'amount',
        'currency_id',
        'withdrawal_status_id',
        'flutterwave_reference',
        'flutterwave_response',
        'failure_reason',
        'auto_processed',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function mobileMoneyAccount()
    {
        return $this->belongsTo(MobileMoneyAccount::class, 'mobile_money_account_id');
    }

    public function status()
    {
        return $this->belongsTo(WithdrawalStatus::class, 'withdrawal_status_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
