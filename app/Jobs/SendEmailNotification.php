<?php

namespace App\Jobs;

use App\Modules\User\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification implements ShouldQueue
{
    use Queueable;

    public User $user;
    public string $subject;
    public string $message;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $subject, string $message)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Envoyer l'email
        Mail::raw($this->message, function ($mail) {
            $mail->to($this->user->email)
                 ->subject($this->subject);
        });
    }
}
