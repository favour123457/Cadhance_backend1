<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomizationRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset' => $this->asset ? new AssetResource($this->asset) : null,
            'template' => $this->template ? new TemplateResource($this->template) : null,
            'user' => new MiniUserResource($this->user),
            'designer' => new MiniUserResource($this->designer),
            'description' => $this->description,
            'reason' => $this->reason,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'customization_status' => new GeneralTypeResource($this->customization_status),
            'milestones' => CustomizationMilestoneResource::collection($this->milestones),
            'price_adjustments' => CustomizationPriceAdjustmentResource::collection($this->price_adjustments),
            'accepted_price_adjustment' => $this->accepted_price_adjustment ? new CustomizationPriceAdjustmentResource($this->accepted_price_adjustment) : null,
            'chat' => $this->chat ? new CustomizationChatResource($this->chat) : null,
            'revisions' => CustomizationRevisionResource::collection($this->revisions),
            'created_at' => $this->created_at,
        ];
    }
}
