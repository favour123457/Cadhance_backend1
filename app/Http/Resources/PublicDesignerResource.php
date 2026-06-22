<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PublicDesignerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->profile;

        $assetAvg = (float) ($this->assets_rating_avg ?? 0);
        $templateAvg = (float) ($this->templates_rating_avg ?? 0);
        $assetReviews = (int) ($this->assets_reviews_sum ?? 0);
        $templateReviews = (int) ($this->templates_reviews_sum ?? 0);
        $totalReviews = $assetReviews + $templateReviews;

        $averageRating = $totalReviews > 0
            ? round((($assetAvg * $assetReviews) + ($templateAvg * $templateReviews)) / $totalReviews, 1)
            : round(max($assetAvg, $templateAvg), 1);

        $isTopFirm = $this->user_subscriptions
            ->contains(function ($subscription) {
                return $subscription->active
                    && $subscription->expire_at >= now()->toDateString()
                    && optional(optional($subscription->subscription_plan)->subscription_type)->name
                    && strtolower(optional(optional($subscription->subscription_plan)->subscription_type)->name) === 'firm';
            });

        $displayName = $profile?->is_studio_name_display_name && $profile?->studio_name
            ? $profile->studio_name
            : $this->name;

        return [
            'id' => $this->id,
            'name' => $displayName,
            'full_name' => $this->name,
            'avatar' => $this->profile_picture ? Storage::disk('r2')->url($this->profile_picture) : null,
            'country' => $this->country?->name,
            'offer_type' => $this->offer_type?->name,
            'bio' => $profile?->bio,
            'studio_name' => $profile?->studio_name,
            'banner_image' => $profile?->banner_image ? Storage::disk('r2')->url($profile->banner_image) : null,
            'primary_role' => $profile?->primary_role ? new GeneralTypeTwoResource($profile->primary_role) : null,
            'design_category' => $profile?->design_category ? new GeneralTypeTwoResource($profile->design_category) : null,
            'assets_count' => (int) ($this->assets_count ?? 0),
            'templates_count' => (int) ($this->templates_count ?? 0),
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
            'is_top_firm' => $isTopFirm,
            'created_at' => $this->created_at,
        ];
    }
}