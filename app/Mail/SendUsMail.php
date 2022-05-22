<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $from_email;
    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($from_email, $content)
    {
        $this->from_email = $from_email;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->from_email)
        ->markdown('emails.sendUsMail')
        ->with(['content' => $this->content]);
    }
}
