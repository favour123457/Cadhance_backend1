<?php

namespace App\Events;

use App\Http\Resources\CustomizationChatMessageResource;
use App\Models\CustomizationChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomizationChatMessage $message;

    public function __construct(CustomizationChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Broadcast on the private chat channel keyed by customization request ID.
     */
    public function broadcastOn(): array
    {
        $chat = $this->message->customization_chat ?? $this->message->customization_chat()->first();

        return [
            new PrivateChannel('chat.' . ($chat?->customization_request_id ?? $this->message->customization_chat_id)),
        ];
    }

    /**
     * The event name the frontend listens for.
     */
    public function broadcastAs(): string
    {
        return 'message.created';
    }

    /**
     * The payload delivered to listeners.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => new CustomizationChatMessageResource($this->message->load('sender_user')),
        ];
    }
}
