<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCustomization extends Model
{
    protected $guarded = [];

    protected $casts = [
        'category_ids' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            CustomizationCategory::class,
            'asset_customization_categories',
            'asset_customization_id',
            'customization_category_id'
        );
    }
}
