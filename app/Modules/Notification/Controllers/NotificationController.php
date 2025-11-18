<?php

namespace App\Modules\Notification\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();

        // Filter by read/unread
        if ($request->has('is_read')) {
            if ($request->boolean('is_read')) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Recent only
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        $notifications = $query->latest()->paginate($request->input('per_page', 20));

        return NotificationResource::collection($notifications);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()->unread()->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Get a specific notification
     */
    public function show($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        // Auto-mark as read when viewed
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return new NotificationResource($notification);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsUnread();

        return new NotificationResource($notification);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $count = Auth::user()->notifications()->read()->delete();

        return response()->json([
            'message' => 'All read notifications deleted',
            'count' => $count,
        ]);
    }

    /**
     * Delete all notifications
     */
    public function deleteAll()
    {
        $count = Auth::user()->notifications()->delete();

        return response()->json([
            'message' => 'All notifications deleted',
            'count' => $count,
        ]);
    }
}
