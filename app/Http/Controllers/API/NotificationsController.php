<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationsResource;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\NotificationType;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationsController extends Controller
{
    public function index()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $notifications = $user->notifications;

        return response()->json(NotificationsResource::collection($notifications));
    }

    public function setting(Request $request){
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $notification_type_id = $request->notification_type_id;
        $status = $request->status;

        $notification_type = NotificationType::find($notification_type_id);
        $notification_setting = NotificationSetting::where('user_id', $user->id)->where('notification_type_id', $notification_type_id)->first();

        if (!$notification_setting) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Notification settings!'
            ], 400);
        }
        
        $notification_setting->update([
            'status' => $status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your '.$notification_type->name.' notification setting has been updated successfully!',
        ]);
    }


    public function readNotification(Request $request){
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $notification_id = $request->notification_id;

        $notification = Notification::find($notification_id);

        if(!$notification || $notification->user_id != $user->id){
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid request',
            ], 403);
        }

        $notification->update([
            'is_read' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your notifications has been marked read!'
        ]);
    }

    public function readAllNotification(Request $request){
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $notifications = $user->notifications();

        $notifications->update([
            'is_read' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your notifications has been mark read!'
        ]);
    }
}
