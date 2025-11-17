<?php

namespace App\Modules\Comment\Models;

use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'signalement_id',
        'user_id',
        'parent_id',
        'content',
        'is_approved',
        'likes',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'likes' => 'integer',
    ];

    protected $with = ['user'];

    // Relationships
    public function signalement()
    {
        return $this->belongsTo(Signalement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Methods
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function like()
    {
        $this->increment('likes');
    }
}
