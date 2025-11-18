<?php

namespace App\Modules\Comment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comment\Models\Comment;
use App\Modules\Comment\Resources\CommentResource;
use App\Modules\Signalement\Models\Signalement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get all comments for a signalement
     */
    public function index($signalementId)
    {
        $signalement = Signalement::findOrFail($signalementId);

        $comments = $signalement->comments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Create a new comment
     */
    public function store(Request $request, $signalementId)
    {
        $signalement = Signalement::findOrFail($signalementId);

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'signalement_id' => $signalement->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true, // Auto-approve, change if moderation needed
        ]);

        // Notification pour le propriétaire du signalement
        if ($signalement->user_id !== Auth::id()) {
            $signalement->user->notifications()->create([
                'type' => 'comment',
                'title' => 'Nouveau commentaire',
                'message' => Auth::user()->name . ' a commenté votre signalement',
                'data' => [
                    'signalement_id' => $signalement->id,
                    'comment_id' => $comment->id,
                ],
                'action_url' => '/signalements/' . $signalement->id,
            ]);
        }

        return new CommentResource($comment->load('user'));
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // Check ownership
        if ($comment->user_id !== Auth::id() && !Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update($validated);

        return new CommentResource($comment->load('user'));
    }

    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Check ownership or moderation rights
        if ($comment->user_id !== Auth::id() && !Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

    /**
     * Like a comment
     */
    public function like($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->like();

        return new CommentResource($comment);
    }

    /**
     * Get replies for a comment
     */
    public function replies($id)
    {
        $comment = Comment::findOrFail($id);
        $replies = $comment->replies()->with('user')->latest()->get();

        return CommentResource::collection($replies);
    }

    /**
     * Approve a comment (Moderator only)
     */
    public function approve($id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return new CommentResource($comment);
    }

    /**
     * Reject a comment (Moderator only)
     */
    public function reject($id)
    {
        if (!Auth::user()->canModerate()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment = Comment::findOrFail($id);
        $comment->update(['is_approved' => false]);

        return new CommentResource($comment);
    }
}
