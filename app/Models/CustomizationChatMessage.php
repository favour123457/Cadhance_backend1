<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\BroadcastsEvents;

class CustomizationChatMessage extends Model
{
    use BroadcastsEvents;

    protected $guarded = [];

    public function customization_chat()
    {
        return $this->belongsTo(CustomizationChat::class);
    }

    public function sender_user()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * The frontend subscribes using the customization request ID (chat.{requestId}),
     * so we broadcast on that same channel instead of the internal chat ID.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(string $event): array
    {
        $chat = $this->customization_chat ?? $this->customization_chat()->first();

        return [
            new PrivateChannel('chat.' . ($chat?->customization_request_id ?? $this->customization_chat_id)),
        ];
    }

    /**
     * Get the data to broadcast for the model.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(string $event): array
    {
        return [
            'message' => new \App\Http\Resources\CustomizationChatMessageResource($this->load('sender_user')),
        ];
    }

    /**
     * The name of the event to broadcast.
     */
    public function broadcastAs(string $event): string
    {
        return 'message.created';
    }
}
