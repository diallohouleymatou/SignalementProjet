<?php

namespace App\Jobs;

use App\Modules\Media\Models\Media;
use App\Services\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessImageUpload implements ShouldQueue
{
    use Queueable;

    public Media $media;
    public int $quality;

    /**
     * Create a new job instance.
     */
    public function __construct(Media $media, int $quality = 85)
    {
        $this->media = $media;
        $this->quality = $quality;
    }

    /**
     * Execute the job.
     */
    public function handle(MediaService $mediaService): void
    {
        // Optimiser l'image
        if ($this->media->isImage()) {
            $mediaService->optimizeImage($this->media, $this->quality);
        }
    }
}
