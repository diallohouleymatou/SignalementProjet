<?php

namespace App\Services;

use App\Modules\Notification\Models\Notification;
use App\Modules\User\Models\User;

class NotificationService
{
    /**
     * Send a notification to a user
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $icon = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'icon' => $icon,
        ]);
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $icon = null
    ): int {
        $count = 0;

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl,
                'icon' => $icon,
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Notification for new message
     */
    public function notifyNewMessage($message)
    {
        $receiver = $message->receiver;
        $sender = $message->sender;

        return $this->send(
            $receiver,
            Notification::TYPE_MESSAGE,
            'Nouveau message',
            "{$sender->name} vous a envoyé un message",
            [
                'message_id' => $message->id,
                'signalement_id' => $message->signalement_id,
                'sender_id' => $sender->id,
            ],
            "/signalements/{$message->signalement_id}/messages",
            'message'
        );
    }

    /**
     * Notification for new comment
     */
    public function notifyNewComment($comment)
    {
        $signalement = $comment->signalement;
        $commenter = $comment->user;

        // Notifier le propriétaire du signalement
        if ($signalement->user_id !== $commenter->id) {
            return $this->send(
                $signalement->user,
                Notification::TYPE_COMMENT,
                'Nouveau commentaire',
                "{$commenter->name} a commenté votre signalement",
                [
                    'comment_id' => $comment->id,
                    'signalement_id' => $signalement->id,
                    'commenter_id' => $commenter->id,
                ],
                "/signalements/{$signalement->id}",
                'comment'
            );
        }

        return null;
    }

    /**
     * Notification for signalement status change
     */
    public function notifySignalementStatusChange($signalement, string $oldStatus, string $newStatus)
    {
        $statusMessages = [
            'en_cours' => 'en cours de recherche',
            'retrouve' => 'retrouvé',
            'faux' => 'marqué comme faux',
        ];

        return $this->send(
            $signalement->user,
            Notification::TYPE_SIGNALEMENT_UPDATED,
            'Statut du signalement mis à jour',
            "Votre signalement a été {$statusMessages[$newStatus]}",
            [
                'signalement_id' => $signalement->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            "/signalements/{$signalement->id}",
            'update'
        );
    }

    /**
     * Notification for moderation action
     */
    public function notifyModerationAction($user, string $action, ?string $reason = null)
    {
        $messages = [
            'approved' => 'Votre contenu a été approuvé',
            'rejected' => 'Votre contenu a été rejeté',
            'banned' => 'Votre compte a été suspendu',
        ];

        $message = $messages[$action] ?? 'Action de modération effectuée';
        if ($reason) {
            $message .= ": {$reason}";
        }

        return $this->send(
            $user,
            Notification::TYPE_MODERATION,
            'Action de modération',
            $message,
            [
                'action' => $action,
                'reason' => $reason,
            ],
            null,
            'shield'
        );
    }

    /**
     * Notification système
     */
    public function notifySystem(User $user, string $title, string $message, ?array $data = null)
    {
        return $this->send(
            $user,
            Notification::TYPE_SYSTEM,
            $title,
            $message,
            $data,
            null,
            'info'
        );
    }

    /**
     * Clear old notifications
     */
    public function clearOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }
}
