<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSubscription extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
