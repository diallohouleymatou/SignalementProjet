<?php

namespace App\Modules\Favorite\Models;

use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'signalement_id',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signalement()
    {
        return $this->belongsTo(Signalement::class);
    }
}
