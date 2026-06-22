<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = [];

    public function role(){
        return $this->belongsTo(Role::class);
    }
}
