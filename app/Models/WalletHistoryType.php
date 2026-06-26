<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistoryType extends Model
{
    protected $guarded = [];

    public const CREDIT = 1;
    public const DEBIT  = 2;
}
