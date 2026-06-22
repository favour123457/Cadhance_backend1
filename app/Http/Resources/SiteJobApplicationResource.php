<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteJobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'site_job' => new SiteJobResource($this->site_job),
            'user' => new UsersResource($this->user),
            'application_date' => $this->application_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
