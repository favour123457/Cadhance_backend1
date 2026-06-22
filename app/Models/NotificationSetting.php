<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $guarded = [];

    public function notification_type()
    {
        return $this->belongsTo(NotificationType::class);
    }
}
