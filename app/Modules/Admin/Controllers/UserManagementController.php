<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Get all users with filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->byRole($request->role);
        }

        // Filter by status
        if ($request->has('verified')) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        if ($request->has('banned')) {
            if ($request->boolean('banned')) {
                $query->banned();
            } else {
                $query->active();
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Order
        $orderBy = $request->input('order_by', 'created_at');
        $orderDir = $request->input('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $users = $query->paginate($request->input('per_page', 20));

        return UserResource::collection($users);
    }

    /**
     * Get a specific user
     */
    public function show($id)
    {
        $user = User::withCount(['signalements', 'favorites', 'comments'])
            ->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string',
            'role' => 'sometimes|in:user,moderator,admin',
            'is_verified' => 'sometimes|boolean',
            'is_banned' => 'sometimes|boolean',
            'bio' => 'nullable|string|max:1000',
            'city' => 'nullable|string',
        ]);

        $user->update($validated);

        return new UserResource($user);
    }

    /**
     * Ban a user
     */
    public function ban(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot ban an admin'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->ban($validated['reason']);

        return new UserResource($user);
    }

    /**
     * Unban a user
     */
    public function unban($id)
    {
        $user = User::findOrFail($id);
        $user->unban();

        return new UserResource($user);
    }

    /**
     * Verify a user
     */
    public function verify($id)
    {
        $user = User::findOrFail($id);
        $user->verify();

        return new UserResource($user);
    }

    /**
     * Change user role
     */
    public function changeRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|in:user,moderator,admin',
        ]);

        $user->update(['role' => $validated['role']]);

        return new UserResource($user);
    }

    /**
     * Delete a user (soft delete)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot delete an admin'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Permanently delete a user
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Cannot delete an admin'], 422);
        }

        $user->forceDelete();

        return response()->json(['message' => 'User permanently deleted']);
    }

    /**
     * Restore a deleted user
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return new UserResource($user);
    }
}
