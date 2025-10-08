<?php

namespace App\Modules\Signalement\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SignalementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'date_loss' => $this->date_loss,
            'location' => $this->location,
            'photos' => $this->photos,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

