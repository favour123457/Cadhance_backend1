<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizationRevision extends Model
{
    protected $guarded = [];

    public function customization_request()
    {
        return $this->belongsTo(CustomizationRequest::class);
    }

    public function requested_by_user()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function responded_by_user()
    {
        return $this->belongsTo(User::class, 'responded_by_user_id');
    }
}
