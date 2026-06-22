<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $guarded = [];

    public function subscription_type()
    {
        return $this->belongsTo(SubscriptionType::class);
    }
}
