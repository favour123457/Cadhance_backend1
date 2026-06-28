<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Template extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_pinned'       => 'boolean',
        'has_video'       => 'boolean',
        'has_sample'      => 'boolean',
        'conversion_rate' => 'float',
        'rank_score'      => 'float',
        'favorite_count'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template_status()
    {
        return $this->belongsTo(TemplateStatus::class);
    }

    public function template_files()
    {
        return $this->hasMany(TemplateFile::class);
    }

    /**
     * Calculate and store the ranking score based on the client-approved formula.
     *
     * Template Score =
     *   0.30*Relevance  (handled at query time via search)
     * + 0.18*ConversionScore
     * + 0.15*ReviewScore  (Bayesian weighted)
     * + 0.12*Quality
     * + 0.10*PurchaseScore
     * + 0.07*Freshness
     * + 0.05*ViewScore
     * + 0.03*SubscriptionBoost
     *
     * Pinned items are sorted to the top by admin and bypass this score.
     */
    public function computeAndSaveRankScore(): void
    {
        // --- Performance signals (log-scaled to prevent dominance) ---
        $purchaseScore   = log($this->purchase_count + 1) * 10;   // max reasonable ~50
        $viewScore       = log($this->views + 1) * 5;
        $conversionScore = min($this->conversion_rate * 100, 100); // 0-100

        // --- Trust / Review score (Bayesian) ---
        $m              = 10;   // minimum review threshold
        $C              = 4.0;  // assumed platform average rating
        $R              = $this->rating ?? 0;
        $v              = $this->review_count ?? 0;
        $bayesianRating = ($v / ($v + $m)) * $R + ($m / ($v + $m)) * $C;
        $reviewScore    = $bayesianRating * 10; // scale to ~0-50

        // --- Quality score ---
        $qualityScore = 0;
        if ($this->thumbnail)           $qualityScore += 5;
        if ($this->images)              $qualityScore += 5;
        if ($this->has_video)           $qualityScore += 10;
        if ($this->has_sample)          $qualityScore += 8;
        if ($this->description && strlen($this->description) > 100) $qualityScore += 6;
        if ($this->includes)            $qualityScore += 4;
        if ($this->price > 0)           $qualityScore += 2;
        // Normalize quality to 0-100
        $qualityScore = min($qualityScore, 40) / 40 * 100;

        // --- Freshness score ---
        $daysSinceCreated  = now()->diffInDays($this->created_at);
        $daysSinceUpdated  = now()->diffInDays($this->updated_at);
        $freshnessScore    = 0;
        if ($daysSinceCreated <= 7)  $freshnessScore = 100;
        elseif ($daysSinceCreated <= 30) $freshnessScore = 60;
        elseif ($daysSinceUpdated <= 30) $freshnessScore = 30;

        // --- Subscription boost (capped, cannot overpower organic signals) ---
        $boostCapped = min($this->subscription_boost ?? 0, 10);

        // --- Weighted sum (relevance handled at query time) ---
        $score =
            ($conversionScore * 0.18) +
            ($reviewScore     * 0.15) +
            ($qualityScore    * 0.12) +
            ($purchaseScore   * 0.10) +
            ($freshnessScore  * 0.07) +
            ($viewScore       * 0.05) +
            ($boostCapped     * 0.03);

        $this->rank_score = round($score, 4);
        $this->saveQuietly();
    }

    /**
     * Scope: marketplace listing order.
     * Pinned templates always come first (sorted by pin_position ASC),
     * then organic results sorted by rank_score DESC.
     */
    public function scopeMarketplaceOrder($query)
    {
        return $query->orderByRaw('is_pinned DESC, pin_position ASC, rank_score DESC');
    }
}
