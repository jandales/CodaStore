<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Concerns\SendsInBackground;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** A contact-form message, sent to the store; replying goes to the visitor. */
class SendUsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsInBackground;

    public string $senderEmail;
    public string $content;

    public function __construct(string $senderEmail, string $content)
    {
        $this->senderEmail = $senderEmail;
        $this->content = $content;
    }

    public function build()
    {
        return $this->subject('New message from ' . $this->senderEmail)
            ->replyTo($this->senderEmail)
            ->markdown('emails.sendUsMail', ['senderEmail' => $this->senderEmail, 'content' => $this->content]);
    }
}
