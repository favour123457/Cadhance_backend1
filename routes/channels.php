<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{customizationRequestId}', function ($user, $customizationRequestId) {
    $chat = \App\Models\CustomizationChat::where('customization_request_id', $customizationRequestId)->first();
    if (!$chat) return false;

    return (int) $user->id === (int) $chat->client_user_id ||
           (int) $user->id === (int) $chat->owner_user_id;
});
