<?php

namespace App\Modules\Favorite\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Favorite\Models\Favorite;
use App\Modules\Signalement\Models\Signalement;
use App\Modules\Signalement\Resources\SignalementResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Get all favorites for authenticated user
     */
    public function index(Request $request)
    {
        $favorites = Auth::user()->favorites()
            ->with('signalement.user', 'signalement.categories', 'signalement.media')
            ->latest()
            ->paginate($request->input('per_page', 20));

        return SignalementResource::collection(
            $favorites->pluck('signalement')
        );
    }

    /**
     * Add a signalement to favorites
     */
    public function store($signalementId)
    {
        $signalement = Signalement::findOrFail($signalementId);

        // Check if already favorited
        $existing = Favorite::where('user_id', Auth::id())
            ->where('signalement_id', $signalement->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Signalement already in favorites'
            ], 422);
        }

        Favorite::create([
            'user_id' => Auth::id(),
            'signalement_id' => $signalement->id,
        ]);

        return response()->json([
            'message' => 'Signalement added to favorites',
            'is_favorited' => true,
        ]);
    }

    /**
     * Remove a signalement from favorites
     */
    public function destroy($signalementId)
    {
        $favorite = Favorite::where('user_id', Auth::id())
            ->where('signalement_id', $signalementId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Signalement not in favorites'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Signalement removed from favorites',
            'is_favorited' => false,
        ]);
    }

    /**
     * Toggle favorite status
     */
    public function toggle($signalementId)
    {
        $signalement = Signalement::findOrFail($signalementId);

        $favorite = Favorite::where('user_id', Auth::id())
            ->where('signalement_id', $signalement->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'message' => 'Signalement removed from favorites',
                'is_favorited' => false,
            ]);
        } else {
            Favorite::create([
                'user_id' => Auth::id(),
                'signalement_id' => $signalement->id,
            ]);

            return response()->json([
                'message' => 'Signalement added to favorites',
                'is_favorited' => true,
            ]);
        }
    }

    /**
     * Check if signalement is favorited
     */
    public function check($signalementId)
    {
        $isFavorited = Favorite::where('user_id', Auth::id())
            ->where('signalement_id', $signalementId)
            ->exists();

        return response()->json(['is_favorited' => $isFavorited]);
    }

    /**
     * Get favorites count
     */
    public function count()
    {
        $count = Auth::user()->favorites()->count();

        return response()->json(['favorites_count' => $count]);
    }

    /**
     * Clear all favorites
     */
    public function clear()
    {
        $count = Auth::user()->favorites()->delete();

        return response()->json([
            'message' => 'All favorites cleared',
            'count' => $count,
        ]);
    }
}
