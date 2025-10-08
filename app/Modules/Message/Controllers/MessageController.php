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
}

