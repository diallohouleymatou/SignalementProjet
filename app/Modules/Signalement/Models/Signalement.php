<?php

namespace App\Modules\Signalement\Models;

use App\Modules\Category\Models\Category;
use App\Modules\Comment\Models\Comment;
use App\Modules\Favorite\Models\Favorite;
use App\Modules\Media\Models\Media;
use App\Modules\Message\Models\Message;
use App\Modules\Report\Models\Report;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signalement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'description',
        'date_loss',
        'location',
        'latitude',
        'longitude',
        'city',
        'country',
        'photos',
        'status',
        'views',
        'shares',
        'reward_amount',
        'reward_currency',
        'contact_phone',
        'contact_email',
        'is_approved',
        'approved_at',
        'approved_by',
        'user_id',
    ];

    protected $casts = [
        'photos' => 'array',
        'date_loss' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'reward_amount' => 'decimal:2',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'views' => 'integer',
        'shares' => 'integer',
    ];

    protected $appends = ['is_favorited', 'favorites_count', 'comments_count'];

    // Type constants
    const TYPE_OBJET = 'objet';
    const TYPE_PERSONNE = 'personne';

    // Status constants
    const STATUS_EN_COURS = 'en_cours';
    const STATUS_RETROUVE = 'retrouve';
    const STATUS_FAUX = 'faux';

    // Boot method for events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($signalement) {
            $signalement->is_approved = true; // Auto-approve by default
            $signalement->approved_at = now();
        });

        static::created(function ($signalement) {
            // Increment user's total signalements
            $signalement->user->increment('total_signalements');
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_signalement');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->ordered();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->approved()->roots();
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNearby($query, float $latitude, float $longitude, float $radius = 10)
    {
        // Radius in kilometers
        // Using Haversine formula
        $query->selectRaw("
            *,
            (
                6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
        ->having('distance', '<=', $radius)
        ->orderBy('distance');

        return $query;
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query)
    {
        return $query->orderBy('views', 'desc');
    }

    // Accessors
    public function getIsFavoritedAttribute()
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->favorites()->where('user_id', auth()->id())->exists();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites()->count();
    }

    public function getCommentsCountAttribute()
    {
        return $this->allComments()->approved()->count();
    }

    public function getHasRewardAttribute()
    {
        return !is_null($this->reward_amount) && $this->reward_amount > 0;
    }

    public function getHasCoordinatesAttribute()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementShares()
    {
        $this->increment('shares');
    }

    public function approve(int $approverId)
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $approverId,
        ]);
    }

    public function reject()
    {
        $this->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function markAsFound()
    {
        $this->update(['status' => self::STATUS_RETROUVE]);
        $this->user->increment('successful_finds');
    }

    public function isFavoritedBy(int $userId): bool
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }

    public function getDistance(float $latitude, float $longitude): float
    {
        if (!$this->has_coordinates) {
            return 0;
        }

        // Haversine formula
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

