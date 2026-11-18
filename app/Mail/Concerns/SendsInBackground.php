<?php

namespace App\Mail\Concerns;

use Illuminate\Contracts\Queue\Factory as Queue;

/**
 * Every store email is sent by the queue worker on the "emails" queue, never while a page
 * waits: 3 tries (after 1, 5 and 15 minutes), at most 30 seconds per try. The queue only
 * picks it up after the database save it belongs to is committed (config/queue.php).
 */
trait SendsInBackground
{
    public $tries = 3;

    public $timeout = 30;

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function queue(Queue $queue)
    {
        if (!$this->queue) {
            $this->onQueue(self::emailQueue());
        }

        return parent::queue($queue);
    }

    public static function emailQueue(): string
    {
        return 'emails';
    }
}
