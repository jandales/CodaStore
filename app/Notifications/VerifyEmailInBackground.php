<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/** The confirm-your-email link, sent by the queue worker so sign-up never waits for the mail server. */
class VerifyEmailInBackground extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 30;

    public function __construct()
    {
        $this->onQueue('emails');
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }
}
