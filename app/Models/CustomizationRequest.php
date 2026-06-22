<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizationRequest extends Model
{
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function customization_status()
    {
        return $this->belongsTo(CustomizationStatus::class);
    }

    public function milestones()
    {
        return $this->hasMany(CustomizationMilestone::class);
    }

    public function price_adjustments()
    {
        return $this->hasMany(CustomizationPriceAdjustment::class);
    }

    public function accepted_price_adjustment()
    {
        return $this->belongsTo(CustomizationPriceAdjustment::class, 'accepted_price_adjustment_id');
    }

    public function chat()
    {
        return $this->hasOne(CustomizationChat::class);
    }

    public function revisions()
    {
        return $this->hasMany(CustomizationRevision::class)->orderBy('created_at', 'desc');
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class, 'customization_request_id');
    }
}
