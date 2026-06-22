<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteJob extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site_job_status()
    {
        return $this->belongsTo(SiteJobStatus::class);
    }

    public function site_job_applications()
    {
        return $this->hasMany(SiteJobApplication::class);
    }
}
