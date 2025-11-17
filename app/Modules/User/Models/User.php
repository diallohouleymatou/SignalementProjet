<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'profile_photo',
        'bio',
        'city',
        'country',
        'is_verified',
        'phone_verified',
        'phone_verified_at',
        'reputation_score',
        'total_signalements',
        'successful_finds',
        'notification_settings',
        'privacy_settings',
        'is_banned',
        'banned_at',
        'ban_reason',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'is_verified' => 'boolean',
            'phone_verified' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'reputation_score' => 'integer',
            'total_signalements' => 'integer',
            'successful_finds' => 'integer',
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
        ];
    }

    // Required by JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function signalements()
    {
        return $this->hasMany(\App\Modules\Signalement\Models\Signalement::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(\App\Modules\Message\Models\Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(\App\Modules\Message\Models\Message::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(\App\Modules\Notification\Models\Notification::class);
    }

    public function comments()
    {
        return $this->hasMany(\App\Modules\Comment\Models\Comment::class);
    }

    public function favorites()
    {
        return $this->hasMany(\App\Modules\Favorite\Models\Favorite::class);
    }

    public function reports()
    {
        return $this->hasMany(\App\Modules\Report\Models\Report::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(\App\Modules\ActivityLog\Models\ActivityLog::class);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_banned', false);
    }

    public function scopeBanned($query)
    {
        return $query->where('is_banned', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // Methods
    public function ban(string $reason)
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $reason,
        ]);
    }

    public function unban()
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
        ]);
    }

    public function verify()
    {
        $this->update(['is_verified' => true]);
    }

    public function verifyPhone()
    {
        $this->update([
            'phone_verified' => true,
            'phone_verified_at' => now(),
        ]);
    }

    public function updateLastSeen()
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function canModerate(): bool
    {
        return $this->isModerator() && !$this->is_banned;
    }

    public function hasUnreadNotifications(): bool
    {
        return $this->notifications()->unread()->exists();
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->unread()->count();
    }
}
