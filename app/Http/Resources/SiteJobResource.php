<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SiteJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UsersResource($this->user),
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'deadline' => $this->deadline,
            'min_salary' => $this->min_salary,
            'max_salary' => $this->max_salary,
            'salary_type' => $this->salary_type,
            'link' => $this->link,
            'contact_email' => $this->contact_email,
            'image' => $this->image ? Storage::disk('r2')->url($this->image) : null,
            'site_job_status' => new GeneralTypeResource($this->site_job_status),
            'created_at' => $this->created_at,
        ];
    }
}
