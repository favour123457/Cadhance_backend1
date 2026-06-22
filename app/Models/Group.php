<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
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

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function group_status()
    {
        return $this->belongsTo(GroupStatus::class);
    }

    public function group_subscriptions()
    {
        return $this->hasMany(GroupSubscription::class);
    }

    /**
     * Calculate and store the ranking score for groups.
     * Group Score = 0.28*Relevance + 0.18*ConversionScore + 0.15*ReviewScore + 0.12*ActivityScore + 0.10*Quality + 0.07*Freshness + 0.05*MemberGrowth + 0.05*SubscriptionBoost
     * Pinned items are sorted to the top by admin and bypass this score.
     */
    public function computeAndSaveRankScore(): void
    {
        $conversionScore = min($this->conversion_rate * 100, 100);
        $reviewScore     = ($this->review_count ?? 0) * 2;
        $activityScore   = log($this->views + 1) * 8;
        $qualityScore    = 0;
        if ($this->has_video) $qualityScore += 10;
        if ($this->has_sample) $qualityScore += 8;
        if ($this->description && strlen($this->description) > 100) $qualityScore += 6;
        $qualityScore = min($qualityScore, 40) / 40 * 100;
        $memberGrowth  = log($this->member_growth + 1) * 10;
        $daysSinceCreated = now()->diffInDays($this->created_at);
        $daysSinceUpdated = now()->diffInDays($this->updated_at);
        $freshnessScore = 0;
        if ($daysSinceCreated <= 7) $freshnessScore = 100;
        elseif ($daysSinceCreated <= 30) $freshnessScore = 60;
        elseif ($daysSinceUpdated <= 30) $freshnessScore = 30;
        $boostCapped = min($this->subscription_boost ?? 0, 10);
        $score =
            ($conversionScore * 0.18) +
            ($reviewScore     * 0.15) +
            ($activityScore   * 0.12) +
            ($qualityScore    * 0.10) +
            ($freshnessScore  * 0.07) +
            ($memberGrowth    * 0.05) +
            ($boostCapped     * 0.05);
        $this->rank_score = round($score, 4);
        $this->saveQuietly();
    }

    public function scopeMarketplaceOrder($query)
    {
        return $query->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC');
    }
}
