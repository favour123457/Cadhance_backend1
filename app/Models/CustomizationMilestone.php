<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizationMilestone extends Model
{
    protected $guarded = [];

    public function customization_request()
    {
        return $this->belongsTo(CustomizationRequest::class);
    }
}
