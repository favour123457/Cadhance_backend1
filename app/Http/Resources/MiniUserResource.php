<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MiniUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUserId = auth()->id();

        $assetAvg = (float) ($this->assets_rating_avg ?? 0);
        $templateAvg = (float) ($this->templates_rating_avg ?? 0);
        $assetReviews = (int) ($this->assets_reviews_sum ?? 0);
        $templateReviews = (int) ($this->templates_reviews_sum ?? 0);
        $totalReviews = $assetReviews + $templateReviews;

        $averageRating = $totalReviews > 0
            ? round((($assetAvg * $assetReviews) + ($templateAvg * $templateReviews)) / $totalReviews, 1)
            : round(max($assetAvg, $templateAvg), 1);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone == null ? '' : $this->phone,
            'email' => $this->email,
            'country' => $this->country ? new MiniCountryResource($this->country) : null,
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
            'account_type' => new GeneralTypeTwoResource($this->account_type),
            'offer_type' => new GeneralTypeTwoResource($this->offer_type),
            'profile_picture' => $this->profile_picture ? Storage::disk('r2')->url($this->profile_picture) : null,
            'profile' => new UserProfileResource($this->profile),
            'is_current_user' => $authUserId === $this->id,
        ];
    }
}
