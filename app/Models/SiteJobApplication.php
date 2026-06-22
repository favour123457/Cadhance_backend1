<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteJobApplication extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site_job()
    {
        return $this->belongsTo(SiteJob::class);
    }
}
