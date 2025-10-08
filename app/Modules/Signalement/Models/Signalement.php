<?php

namespace App\Modules\Signalement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signalement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'date_loss',
        'location',
        'photos',
        'status',
        'user_id',
    ];

    protected $casts = [
        'photos' => 'array',
        'date_loss' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class);
    }
}

