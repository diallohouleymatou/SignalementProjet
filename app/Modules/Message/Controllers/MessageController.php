<?php

namespace App\Modules\Message\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Message\Models\Message;
use App\Modules\Message\Requests\MessageRequest;
use App\Modules\Message\Resources\MessageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(MessageRequest $request)
    {
        $data = $request->validated();
        $data['sender_id'] = Auth::id();
        $message = Message::create($data);
        return new MessageResource($message);
    }

    public function index($signalement_id)
    {
        $messages = Message::where('signalement_id', $signalement_id)->orderBy('created_at')->get();
        return MessageResource::collection($messages);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        $message->read = true;
        $message->save();
        return new MessageResource($message);
    }

    // Get all conversations for authenticated user
    public function conversations()
    {
        $userId = Auth::id();

        // Get all messages where user is sender or receiver
        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver', 'signalement'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by conversation partner
        $conversations = $messages->groupBy(function($message) use ($userId) {
            return $message->sender_id == $userId
                ? $message->receiver_id
                : $message->sender_id;
        });

        return response()->json([
            'conversations' => $conversations->map(function($messages) {
                return [
                    'partner' => $messages->first()->sender_id == Auth::id()
                        ? $messages->first()->receiver
                        : $messages->first()->sender,
                    'last_message' => new MessageResource($messages->first()),
                    'unread_count' => $messages->where('receiver_id', Auth::id())
                        ->where('read', false)
                        ->count(),
                ];
            })->values()
        ]);
    }

    // Get conversation with a specific user about a signalement
    public function conversationWith($signalement_id, $user_id)
    {
        $userId = Auth::id();

        $messages = Message::where('signalement_id', $signalement_id)
            ->where(function($query) use ($userId, $user_id) {
                $query->where(function($q) use ($userId, $user_id) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $user_id);
                })->orWhere(function($q) use ($userId, $user_id) {
                    $q->where('sender_id', $user_id)
                      ->where('receiver_id', $userId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return MessageResource::collection($messages);
    }

    // Get unread messages count
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // Mark all messages from a user as read
    public function markAllAsRead($signalement_id)
    {
        Message::where('signalement_id', $signalement_id)
            ->where('receiver_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['message' => 'All messages marked as read']);
    }
}

