<?php

namespace App\Modules\Media\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'human_size' => $this->human_size,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'is_image' => $this->isImage(),
            'metadata' => $this->metadata,
            'order' => $this->order,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
