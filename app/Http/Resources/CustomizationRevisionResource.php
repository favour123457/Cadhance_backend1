<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomizationRevisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customization_request_id' => $this->customization_request_id,
            'requested_by' => new MiniUserResource($this->requested_by_user),
            'note' => $this->note,
            'attachment' => $this->attachment ? Storage::disk('r2')->url($this->attachment) : null,
            'attachment_name' => $this->attachment_name,
            'status' => $this->status,
            'decision_reason' => $this->decision_reason,
            'responded_by_user_id' => $this->responded_by_user_id,
            'responded_at' => $this->responded_at,
            'created_at' => $this->created_at,
        ];
    }
}
