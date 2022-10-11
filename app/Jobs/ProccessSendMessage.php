<?php

namespace App\Jobs;

use App\Mail\SendUsMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProccessSendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $email;
    private $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $content)
    {      
        $this->email = $email;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      
        Mail::to($this->email)->send(new SendUsMail($this->content));
    }

  
}
