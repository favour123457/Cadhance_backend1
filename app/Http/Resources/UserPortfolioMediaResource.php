<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserPortfolioMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => new GeneralTypeResource($this->document_type),
            'url' => $this->url ? Storage::disk('r2')->url($this->url) : null,
            'created_at' => $this->created_at,
        ];
    }
}
