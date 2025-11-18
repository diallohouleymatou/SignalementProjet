<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanOldNotifications implements ShouldQueue
{
    use Queueable;

    public int $days;

    /**
     * Create a new job instance.
     */
    public function __construct(int $days = 30)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $notificationService->clearOldNotifications($this->days);
    }
}
