<?php

namespace App\Http\Resources;

use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    /**
     * When $private is true (owner viewing their own profile),
     * social_links is always returned regardless of subscription.
     */
    public function __construct($resource, public bool $private = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        // Social links: always visible to the owner; gated by subscription for public views
        $hasActiveSub = $this->private || UserSubscription::where('user_id', $this->user_id)
            ->where('active', true)
            ->where('expire_at', '>=', now()->toDateString())
            ->exists();

        $user = $this->user;
        $assetAvg = $user ? (float) ($user->assets_rating_avg ?? 0) : 0;
        $templateAvg = $user ? (float) ($user->templates_rating_avg ?? 0) : 0;
        $assetReviews = $user ? (int) ($user->assets_reviews_sum ?? 0) : 0;
        $templateReviews = $user ? (int) ($user->templates_reviews_sum ?? 0) : 0;
        $totalReviews = $assetReviews + $templateReviews;

        $averageRating = $totalReviews > 0
            ? round((($assetAvg * $assetReviews) + ($templateAvg * $templateReviews)) / $totalReviews, 1)
            : round(max($assetAvg, $templateAvg), 1);

        return [
            'id'                          => $this->id,
            'user_id'                     => $this->user_id,
            'bio'                         => $this->bio,
            'visibility'                  => $this->visibility,
            'design_category'             => $this->design_category ? new GeneralTypeTwoResource($this->design_category) : null,
            'social_links'                => $hasActiveSub ? $this->social_links : null,
            'primary_role'                => $this->primary_role ? new GeneralTypeTwoResource($this->primary_role) : null,
            'studio_name'                 => $this->studio_name,
            'banner_image'                => $this->banner_image ? Storage::disk('r2')->url($this->banner_image) : null,
            'profile_picture'             => $user && $user->profile_picture ? Storage::disk('r2')->url($user->profile_picture) : null,
            'display_name'                => $this->is_studio_name_display_name ? $this->studio_name : ($user ? $user->name : null),
            'is_studio_name_display_name' => $this->is_studio_name_display_name,
            'country'                     => $user && $user->country ? new MiniCountryResource($user->country) : null,
            'average_rating'              => $averageRating,
            'total_reviews'               => $totalReviews,
            'created_at'                  => $this->created_at,
        ];
    }
}
