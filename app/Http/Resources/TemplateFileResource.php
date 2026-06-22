<?php

namespace App\Http\Resources;

use App\Models\UserPurchase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class TemplateFileResource extends JsonResource
{
    /** Cached authenticated user for the lifetime of this PHP request. */
    private static mixed $cachedAuthUser = 'unchecked';

    public function toArray(Request $request): array
    {
        $user = $this->resolveAuthUser();
        $canAccess = $this->isAuthorized();
        
        // Get the parent template to check ownership
        $template = $this->resource->relationLoaded('template')
            ? $this->resource->getRelation('template')
            : null;
        
        $isOwner = $template && $user && $template->user_id == $user->id;

        return [
            'id'           => $this->id,
            'template_id'  => $this->template_id,
            'file_name'    => $this->file_name,
            // Expose file_path for preview files OR for the owner (so they can edit)
            'file_path'    => ($this->is_preview || $isOwner)
                                ? Storage::disk('r2')->url($this->file_path)
                                : null,
            'download_url' => $canAccess
                                ? url('api/templates/file/' . $this->id . '/download')
                                : null,
            'is_preview'   => $this->is_preview,
            'created_at'   => $this->created_at,
        ];
    }

    // -------------------------------------------------------------------------

    private function isAuthorized(): bool
    {
        if ($this->is_preview) {
            return true;
        }

        $user = $this->resolveAuthUser();
        if (!$user) {
            return false;
        }

        $template = $this->resource->relationLoaded('template')
            ? $this->resource->getRelation('template')
            : null;

        if ($template && $template->price == 0) {
            return true;
        }

        if ($template && $template->user_id == $user->id) {
            return true;
        }

        return UserPurchase::where('user_id', $user->id)
            ->where('purchasable_type', 'template')
            ->where('purchasable_id', $this->template_id)
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
