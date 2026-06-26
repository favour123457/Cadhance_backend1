<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistoryStatus extends Model
{
    protected $guarded = [];

    public const PENDING = 1;
    public const SUCCESS = 2;
    public const FAILED  = 3;
}
