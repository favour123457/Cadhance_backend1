<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileMoneyAccount extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'provider',
        'network_code',
        'account_name',
        'account_number',
        'is_verified',
        'recipient_address',
        'recipient_email',
        'recipient_country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
