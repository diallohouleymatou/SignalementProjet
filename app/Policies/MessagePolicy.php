<?php

namespace App\Policies;

use App\Modules\Message\Models\Message;
use App\Modules\User\Models\User;

class MessagePolicy
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
    public function view(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id
            || $user->id === $message->receiver_id
            || $user->canModerate();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return !$user->is_banned;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id || $user->canModerate();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->id === $message->sender_id
            || $user->id === $message->receiver_id
            || $user->canModerate();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Message $message): bool
    {
        return $user->canModerate();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can mark message as read.
     */
    public function markAsRead(User $user, Message $message): bool
    {
        return $user->id === $message->receiver_id;
    }
}
