<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_pinned'       => 'boolean',
        'has_video'       => 'boolean',
        'has_sample'      => 'boolean',
        'conversion_rate' => 'float',
        'rank_score'      => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function design_category()
    {
        return $this->belongsTo(DesignCategory::class);
    }

    public function license_type()
    {
        return $this->belongsTo(LicenseType::class);
    }

    public function asset_status()
    {
        return $this->belongsTo(AssetStatus::class);
    }

    public function asset_files()
    {
        return $this->hasMany(AssetFile::class);
    }

    public function specifications()
    {
        return $this->hasMany(AssetSpecification::class);
    }

    /**
     * Calculate and store the ranking score for assets.
     * Asset Score = 0.35*Relevance + 0.15*Quality + 0.20*PurchaseScore + 0.10*ReviewScore + 0.08*SaveLikeScore + 0.05*ViewScore + 0.04*Freshness + 0.03*SubscriptionBoost
     * Pinned items are sorted to the top by admin and bypass this score.
     */
    public function computeAndSaveRankScore(): void
    {
        $purchaseScore   = log($this->purchase_count + 1) * 10;
        $viewScore       = log($this->views + 1) * 5;
        $saveLikeScore   = log($this->saves + 1) * 8;
        $reviewScore     = ($this->review_count ?? 0) * 2; // simple, can use Bayesian if ratings exist
        $qualityScore    = 0;
        if ($this->thumbnail) $qualityScore += 5;
        if ($this->images) $qualityScore += 5;
        if ($this->has_video) $qualityScore += 10;
        if ($this->has_sample) $qualityScore += 8;
        if ($this->description && strlen($this->description) > 100) $qualityScore += 6;
        if ($this->price > 0) $qualityScore += 2;
        $qualityScore = min($qualityScore, 40) / 40 * 100;
        $daysSinceCreated = now()->diffInDays($this->created_at);
        $daysSinceUpdated = now()->diffInDays($this->updated_at);
        $freshnessScore = 0;
        if ($daysSinceCreated <= 7) $freshnessScore = 100;
        elseif ($daysSinceCreated <= 30) $freshnessScore = 60;
        elseif ($daysSinceUpdated <= 30) $freshnessScore = 30;
        $boostCapped = min($this->subscription_boost ?? 0, 10);
        $score =
            ($qualityScore    * 0.15) +
            ($purchaseScore   * 0.20) +
            ($reviewScore     * 0.10) +
            ($saveLikeScore   * 0.08) +
            ($viewScore       * 0.05) +
            ($freshnessScore  * 0.04) +
            ($boostCapped     * 0.03);
        $this->rank_score = round($score, 4);
        $this->saveQuietly();
    }

    public function scopeMarketplaceOrder($query)
    {
        return $query->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC');
    }
}
