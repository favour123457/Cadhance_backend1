<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUserId = null;
        $hasPurchased = false;
        $isFavorited = false;
        
        try {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::parseToken();
            $authUser = $token->authenticate();
            if ($authUser) {
                $authUserId = $authUser->id;
                $hasPurchased = \App\Models\UserPurchase::where('user_id', $authUserId)
                    ->where('purchasable_type', 'asset')
                    ->where('purchasable_id', $this->id)
                    ->where('status', 'completed')
                    ->exists();
                $isFavorited = \App\Models\Favorite::where('user_id', $authUserId)
                    ->where('favoriteable_type', 'asset')
                    ->where('favoriteable_id', $this->id)
                    ->exists();
            }
        } catch (\Exception $e) {
            // Not authenticated - that's OK
        }

        return [
            'id' => $this->id,
            'user' => new MiniUserResource($this->user),
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'service_charge' => $this->service_charge,
            'design_category' => new GeneralTypeTwoResource($this->design_category),
            'favorite_count' => $this->favorite_count,
            'download_count' => $this->download_count,
            'view_count' => $this->view_count,
            'thumbnail' => $this->thumbnail ? Storage::disk('r2')->url($this->thumbnail) : null,
            'bought_count' => $this->bought_count,
            'unique_code' => $this->unique_code,
            'rating' => $this->rating,
            'specifications' => $this->specifications,
            'tools_used' => $this->tools_used,
            'available_file_formats' => $this->available_file_formats,
            'license_type' => new GeneralTypeTwoResource($this->license_type),
            'detail_view' => $this->detail_view,
            'visibility' => $this->visibility,
            'affiliate_settings' => $this->affiliate_settings,
            'affiliate_commission_rate' => $this->affiliate_commission_rate,
            'customization_available' => $this->customization_available,
            'customization_price' => $this->customization_price,
            'asset_status' => new GeneralTypeResource($this->asset_status),
            'has_purchased' => $hasPurchased,
            'is_favorited' => $isFavorited,
            // Pre-load the parent Asset onto every AssetFile so AssetFileResource
            // can check ownership without issuing extra queries.
            'files' => $this->asset_files->map(
                fn($file) => new AssetFileResource($file->setRelation('asset', $this->resource))
            ),
            'created_at' => $this->created_at,
        ];
    }
}
