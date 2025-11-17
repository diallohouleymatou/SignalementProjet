<?php

namespace App\Services;

use App\Modules\Media\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaService
{
    /**
     * Upload a file and create media record
     */
    public function upload(UploadedFile $file, $model, string $collection = 'default', array $metadata = []): Media
    {
        // Générer un nom de fichier unique
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Définir le chemin
        $path = $collection . '/' . date('Y/m/d') . '/' . $fileName;

        // Uploader le fichier
        $disk = config('media.disk', 'public');
        Storage::disk($disk)->put($path, file_get_contents($file));

        // Créer la miniature si c'est une image
        $thumbnailPath = null;
        if ($this->isImage($file)) {
            $thumbnailPath = $this->createThumbnail($file, $path, $disk);
        }

        // Créer l'enregistrement Media
        return $model->media()->create([
            'collection' => $collection,
            'name' => $name,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'disk' => $disk,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'size' => $file->getSize(),
            'metadata' => array_merge($metadata, [
                'original_name' => $file->getClientOriginalName(),
            ]),
        ]);
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, $model, string $collection = 'default'): array
    {
        $mediaItems = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $media = $this->upload($file, $model, $collection, ['order' => $index]);
                $mediaItems[] = $media;
            }
        }

        return $mediaItems;
    }

    /**
     * Create thumbnail for image
     */
    protected function createThumbnail(UploadedFile $file, string $originalPath, string $disk): ?string
    {
        try {
            $thumbnailPath = str_replace(
                '.' . $file->getClientOriginalExtension(),
                '_thumb.' . $file->getClientOriginalExtension(),
                $originalPath
            );

            // Si Intervention Image est disponible
            if (class_exists('Intervention\Image\Facades\Image')) {
                $img = Image::make($file);
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                Storage::disk($disk)->put($thumbnailPath, $img->encode());

                return $thumbnailPath;
            }

            // Sinon, copier l'original
            Storage::disk($disk)->copy($originalPath, $thumbnailPath);

            return $thumbnailPath;

        } catch (\Exception $e) {
            // En cas d'erreur, pas de miniature
            return null;
        }
    }

    /**
     * Delete media and its files
     */
    public function delete(Media $media): bool
    {
        try {
            // Supprimer les fichiers du storage
            Storage::disk($media->disk)->delete($media->path);

            if ($media->thumbnail_path) {
                Storage::disk($media->disk)->delete($media->thumbnail_path);
            }

            // Supprimer l'enregistrement
            return $media->delete();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete multiple media
     */
    public function deleteMultiple(array $mediaIds): int
    {
        $deleted = 0;

        foreach ($mediaIds as $id) {
            $media = Media::find($id);
            if ($media && $this->delete($media)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Check if file is an image
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Optimize image (compress)
     */
    public function optimizeImage(Media $media, int $quality = 85): bool
    {
        if (!$media->isImage()) {
            return false;
        }

        try {
            if (!class_exists('Intervention\Image\Facades\Image')) {
                return false;
            }

            $img = Image::make(Storage::disk($media->disk)->get($media->path));
            $img->save(null, $quality);

            Storage::disk($media->disk)->put($media->path, $img->encode());

            // Mettre à jour la taille
            $newSize = Storage::disk($media->disk)->size($media->path);
            $media->update(['size' => $newSize]);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reorder media items
     */
    public function reorder(array $mediaOrder): bool
    {
        try {
            foreach ($mediaOrder as $order => $mediaId) {
                Media::where('id', $mediaId)->update(['order' => $order]);
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get media by collection
     */
    public function getByCollection($model, string $collection)
    {
        return $model->media()->forCollection($collection)->ordered()->get();
    }

    /**
     * Clear collection (delete all media in collection)
     */
    public function clearCollection($model, string $collection): int
    {
        $media = $this->getByCollection($model, $collection);
        $deleted = 0;

        foreach ($media as $item) {
            if ($this->delete($item)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
