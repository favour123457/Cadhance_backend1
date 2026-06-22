<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetFile extends Model
{
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
