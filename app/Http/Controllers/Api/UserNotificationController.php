<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
    
            $new = $user->unreadNotifications;
            $read = $user->readNotifications;
    
            return response()->json([
                'status' => true,
                'new' => $new,
                'read' => $read,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function all(Request $request) {
        try {
            $notifications = $request->user()->notifications;
        
            return response()->json([
                'status' => true,
                'notifications' => $notifications,
            ]);
        } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
    }

    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->input('id');
            $notification = $request->user()->notifications()->find($notificationId);
    
            if (!$notification) {
                return response()->json(['status' => false ,'message' => __('Notification not found')]);
            }
            $notification->markAsRead();
            return response()->json(['status' => true , 'message' => __('Notification marked as read')]);
        } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
    }
}
