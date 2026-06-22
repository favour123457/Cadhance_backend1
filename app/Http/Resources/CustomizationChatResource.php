<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomizationChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customization_request_id' => $this->customization_request_id,
            'title' => $this->title,
            'asset' => $this->asset ? new AssetResource($this->asset) : null,
            'template' => $this->template ? new TemplateResource($this->template) : null,
            'client' => new MiniUserResource($this->client_user),
            'owner' => new MiniUserResource($this->owner_user),
            'last_message_at' => $this->last_message_at,
            'messages' => CustomizationChatMessageResource::collection($this->messages),
            'created_at' => $this->created_at,
        ];
    }
}
