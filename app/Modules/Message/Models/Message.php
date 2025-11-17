<?php

namespace App\Modules\Message\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'signalement_id',
        'sender_id',
        'receiver_id',
        'content',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public function signalement()
    {
        return $this->belongsTo(\App\Modules\Signalement\Models\Signalement::class);
    }

    public function sender()
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'receiver_id');
    }
}
