<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $guarded = [];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function wallet_history_type()
    {
        return $this->belongsTo(WalletHistoryType::class);
    }

    public function wallet_history_status()
    {
        return $this->belongsTo(WalletHistoryStatus::class);
    }
}
