<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizationChat extends Model
{
    protected $guarded = [];

    public function customization_request()
    {
        return $this->belongsTo(CustomizationRequest::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function client_user()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function owner_user()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function messages()
    {
        return $this->hasMany(CustomizationChatMessage::class)->orderBy('created_at', 'asc');
    }
}
