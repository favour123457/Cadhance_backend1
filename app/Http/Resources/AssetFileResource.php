<?php

namespace App\Http\Resources;

use App\Models\UserPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class AssetFileResource extends JsonResource
{
    /**
     * Cache the authenticated user for the lifetime of this PHP request
     * so we only parse the JWT token once per response.
     */
    private static mixed $cachedAuthUser = 'unchecked';

    public function toArray(Request $request): array
    {
        $user = $this->resolveAuthUser();
        $canAccess = $this->isAuthorized();
        
        // Get the parent asset to check ownership
        $asset = $this->resource->relationLoaded('asset')
            ? $this->resource->getRelation('asset')
            : null;
        
        $isOwner = $asset && $user && $asset->user_id == $user->id;

        return [
            'id'           => $this->id,
            'asset_id'     => $this->asset_id,
            'file_name'    => $this->file_name,
            // Expose file_path for preview files OR for the owner (so they can edit)
            'file_path'    => ($this->is_preview || $isOwner) 
                                ? Storage::disk('r2')->url($this->file_path) 
                                : null,
            'download_url' => $canAccess
                                ? url('api/assets/file/' . $this->id . '/download')
                                : null,
            'is_preview'   => $this->is_preview,
            'created_at'   => $this->created_at,
        ];
    }

    // -------------------------------------------------------------------------

    private function isAuthorized(): bool
    {
        // Preview files are freely accessible.
        if ($this->is_preview) {
            return true;
        }

        $user = $this->resolveAuthUser();
        if (!$user) {
            return false;
        }

        // The parent Asset is pre-loaded via setRelation() in AssetResource /
        // AssetController so no extra DB query is needed for the ownership check.
        $asset = $this->resource->relationLoaded('asset')
            ? $this->resource->getRelation('asset')
            : null;

        // Free assets are always accessible to any authenticated user.
        if ($asset && $asset->price == 0) {
            return true;
        }

        // Owner always has access.
        if ($asset && $asset->user_id == $user->id) {
            return true;
        }

        // Purchased?
        return UserPurchase::where('user_id', $user->id)
            ->where('purchasable_type', 'asset')
            ->where('purchasable_id', $this->asset_id)
            ->exists();
    }

    private function resolveAuthUser(): mixed
    {
        if (self::$cachedAuthUser === 'unchecked') {
            try {
                self::$cachedAuthUser = JWTAuth::parseToken()->authenticate();
            } catch (\Exception) {
                self::$cachedAuthUser = null;
            }
        }

        return self::$cachedAuthUser;
    }
}
