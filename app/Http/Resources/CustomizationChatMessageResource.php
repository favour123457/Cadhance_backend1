<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomizationChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender' => new MiniUserResource($this->sender_user),
            'message_type' => $this->message_type,
            'message' => $this->message,
            'attachment' => $this->attachment ? Storage::disk('r2')->url($this->attachment) : null,
            'attachment_name' => $this->attachment_name,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
