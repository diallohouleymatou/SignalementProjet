<?php

namespace App\Modules\Comment\Resources;

use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'signalement_id' => $this->signalement_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'is_approved' => $this->is_approved,
            'likes' => $this->likes,
            'is_reply' => $this->isReply(),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when($this->replies, fn() => $this->replies->count(), 0),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
