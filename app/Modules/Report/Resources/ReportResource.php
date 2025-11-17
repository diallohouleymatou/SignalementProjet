<?php

namespace App\Modules\Report\Resources;

use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'reportable_type' => $this->reportable_type,
            'reportable_id' => $this->reportable_id,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status,
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'admin_notes' => $this->when($request->user()?->canModerate(), $this->admin_notes),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
