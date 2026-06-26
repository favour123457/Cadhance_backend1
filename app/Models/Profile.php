<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function design_category()
    {
        return $this->belongsTo(DesignCategory::class);
    }

    public function design_categories()
    {
        return $this->belongsToMany(DesignCategory::class)->withTimestamps();
    }

    public function primary_role()
    {
        return $this->belongsTo(PrimaryRole::class);
    }
}
