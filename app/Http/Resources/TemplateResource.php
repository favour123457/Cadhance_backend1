<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUserId = null;
        $isFavorited = false;

        try {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::parseToken();
            $authUser = $token->authenticate();
            if ($authUser) {
                $authUserId = $authUser->id;
                $isFavorited = \App\Models\Favorite::where('user_id', $authUserId)
                    ->where('favoriteable_type', 'template')
                    ->where('favoriteable_id', $this->id)
                    ->exists();
            }
        } catch (\Exception $e) {
            // Not authenticated - that's OK
        }

        return [
            'id'              => $this->id,
            'user'            => new UsersResource($this->user),
            'title'           => $this->title,
            'description'     => $this->description,
            'includes'        => $this->includes,
            'price'           => $this->price,
            'favorite_count'  => $this->favorite_count ?? 0,
            'download_count'  => $this->download_count,
            'thumbnail'       => $this->thumbnail ? Storage::disk('r2')->url($this->thumbnail) : null,
            'images'          => $this->images
                ? array_map(
                    fn($p) => Storage::disk('r2')->url(trim($p)),
                    array_filter(explode(',', $this->images))
                )
                : [],
            'rating'          => $this->rating,
            'has_video'       => $this->has_video,
            'has_sample'      => $this->has_sample,
            'is_pinned'       => $this->is_pinned,       // lets mobile/web badge "Featured by Admin"
            'pin_position'    => $this->pin_position,
            'rank_score'      => $this->rank_score,
            'template_status' => new GeneralTypeResource($this->template_status),
            // Pre-load the parent Template onto every TemplateFile so
            // TemplateFileResource can check ownership without extra queries.
            'files'           => $this->template_files->map(
                fn($file) => new TemplateFileResource($file->setRelation('template', $this->resource))
            ),
            'is_favorited'    => $isFavorited,
            'created_at'      => $this->created_at,
        ];
    }
}
