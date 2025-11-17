<?php

namespace App\Modules\Report\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reportable_id',
        'reportable_type',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Report reasons constants
    const REASON_SPAM = 'spam';
    const REASON_INAPPROPRIATE = 'inappropriate';
    const REASON_FAKE = 'fake';
    const REASON_DUPLICATE = 'duplicate';
    const REASON_HARASSMENT = 'harassment';
    const REASON_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reportable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', self::STATUS_REVIEWED);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    // Methods
    public function markAsReviewed(int $reviewerId, ?string $notes = null)
    {
        $this->update([
            'status' => self::STATUS_REVIEWED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function markAsResolved(int $reviewerId, ?string $notes = null)
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function markAsRejected(int $reviewerId, ?string $notes = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}
