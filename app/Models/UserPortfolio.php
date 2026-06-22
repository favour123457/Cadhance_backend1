<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPortfolio extends Model
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

    public function design_type()
    {
        return $this->belongsTo(DesignType::class);
    }

    public function media()
    {
        return $this->hasMany(UserPortfolioMedia::class, 'user_portfolio_id');
    }
}
