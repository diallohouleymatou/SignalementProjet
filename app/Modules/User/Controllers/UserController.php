<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Requests\UserProfileRequest;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Get authenticated user's profile
    public function profile()
    {
        return new UserResource(Auth::user());
    }

    // Update authenticated user's profile
    public function updateProfile(UserProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }

        $user->update($data);
        return new UserResource($user);
    }

    // Get all users (for searching contacts)
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())
            ->latest()
            ->get();
        return UserResource::collection($users);
    }

    // Get specific user by ID
    public function show($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    // Get user statistics
    public function statistics()
    {
        $user = Auth::user();

        $stats = [
            'total_signalements' => $user->signalements()->count(),
            'signalements_en_cours' => $user->signalements()->where('status', 'en_cours')->count(),
            'signalements_retrouves' => $user->signalements()->where('status', 'retrouve')->count(),
            'signalements_faux' => $user->signalements()->where('status', 'faux')->count(),
            'objets_signales' => $user->signalements()->where('type', 'objet')->count(),
            'personnes_signalees' => $user->signalements()->where('type', 'personne')->count(),
            'messages_sent' => $user->sentMessages()->count(),
            'messages_received' => $user->receivedMessages()->count(),
            'unread_messages' => $user->receivedMessages()->where('read', false)->count(),
        ];

        return response()->json($stats);
    }
}

