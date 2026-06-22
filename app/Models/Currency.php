<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'flag',
        'symbol',
        'symbol2',
        'country_id',
        'exchange_rate',
        'is_base_currency',
        'active',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_base_currency' => 'boolean',
        'active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
