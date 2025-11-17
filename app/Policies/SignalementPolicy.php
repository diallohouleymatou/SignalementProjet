<?php

namespace App\Policies;

use App\Modules\Signalement\Models\Signalement;
use App\Modules\User\Models\User;

class SignalementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Signalement $signalement): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Signalement $signalement): bool
    {
        return $user->id === $signalement->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Signalement $signalement): bool
    {
        return $user->id === $signalement->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Signalement $signalement): bool
    {
        return $user->id === $signalement->user_id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Signalement $signalement): bool
    {
        return $user->role === 'admin';
    }
}
