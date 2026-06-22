<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateCommission extends Model
{
    protected $guarded = [];

    public function referrer_user()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred_user()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function user_subscription()
    {
        return $this->belongsTo(UserSubscription::class);
    }

    public function subscription_plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function admin_data()
    {
        return $this->belongsTo(AdminData::class);
    }
}
