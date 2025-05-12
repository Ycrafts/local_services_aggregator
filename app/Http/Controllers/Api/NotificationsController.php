<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    // Fetch all notifications for the authenticated user
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($notifications);
    }

    // Mark a notification as read
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $notification->is_read = true;
        $notification->save();
        return response()->json(['message' => 'Notification marked as read.']);
    }
} 