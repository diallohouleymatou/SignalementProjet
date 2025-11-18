<?php

namespace App\Modules\Media\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Media\Models\Media;
use App\Modules\Media\Resources\MediaResource;
use App\Modules\Signalement\Models\Signalement;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload files for a signalement
     */
    public function uploadForSignalement(Request $request, $signalementId)
    {
        $signalement = Signalement::findOrFail($signalementId);

        // Vérifier que l'utilisateur est le propriétaire
        if ($signalement->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'collection' => 'nullable|string',
        ]);

        $collection = $request->input('collection', 'signalement_photos');
        $uploadedMedia = $this->mediaService->uploadMultiple(
            $request->file('files'),
            $signalement,
            $collection
        );

        return MediaResource::collection($uploadedMedia);
    }

    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB
        ]);

        $user = Auth::user();

        // Supprimer l'ancienne photo de profil
        $oldMedia = $user->media()->forCollection('profile_photos')->first();
        if ($oldMedia) {
            $this->mediaService->delete($oldMedia);
        }

        // Uploader la nouvelle
        $media = $this->mediaService->upload(
            $request->file('photo'),
            $user,
            'profile_photos'
        );

        // Mettre à jour le champ profile_photo de l'utilisateur
        $user->update(['profile_photo' => $media->path]);

        return new MediaResource($media);
    }

    /**
     * Get media for a model
     */
    public function index(Request $request)
    {
        $query = Media::query();

        // Filter by mediable
        if ($request->has('mediable_type') && $request->has('mediable_id')) {
            $query->where('mediable_type', $request->mediable_type)
                  ->where('mediable_id', $request->mediable_id);
        }

        // Filter by collection
        if ($request->has('collection')) {
            $query->forCollection($request->collection);
        }

        // Only images
        if ($request->boolean('images_only')) {
            $query->images();
        }

        $media = $query->ordered()->get();

        return MediaResource::collection($media);
    }

    /**
     * Get a specific media
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);

        return new MediaResource($media);
    }

    /**
     * Delete a media
     */
    public function destroy($id)
    {
        $media = Media::findOrFail($id);

        // Vérifier les permissions
        $mediable = $media->mediable;
        if ($mediable && method_exists($mediable, 'user_id')) {
            if ($mediable->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $this->mediaService->delete($media);

        return response()->json(['message' => 'Media deleted successfully']);
    }

    /**
     * Reorder media
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:media,id',
        ]);

        $this->mediaService->reorder($request->order);

        return response()->json(['message' => 'Media reordered successfully']);
    }

    /**
     * Optimize an image
     */
    public function optimize($id)
    {
        $media = Media::findOrFail($id);

        if (!$media->isImage()) {
            return response()->json(['message' => 'Only images can be optimized'], 422);
        }

        // Vérifier les permissions
        $mediable = $media->mediable;
        if ($mediable && method_exists($mediable, 'user_id')) {
            if ($mediable->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $optimized = $this->mediaService->optimizeImage($media);

        if ($optimized) {
            return new MediaResource($media->fresh());
        }

        return response()->json(['message' => 'Failed to optimize image'], 500);
    }
}
