<?php

namespace App\Services\Emails;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The background email queue: is it moving, and what failed (Admin > Failed emails).
 */
class EmailQueue
{
    public const QUEUE = 'emails';

    /** An email waiting longer than this means the worker probably isn't running. */
    public const STUCK_MINUTES = 10;

    /** What each email is, in staff words. */
    public const LABELS = [
        \App\Mail\Orders\OrderConfirmedMail::class => 'Order confirmed',
        \App\Mail\Orders\OrderShippedMail::class => 'Order shipped',
        \App\Mail\Orders\OrderCancelledMail::class => 'Order cancelled',
        \App\Mail\Orders\ReturnRequestedMail::class => 'Return requested (to the store)',
        \App\Mail\Orders\ReturnApprovedMail::class => 'Return approved',
        \App\Mail\Orders\ReturnRejectedMail::class => 'Return declined',
        \App\Mail\NewsletterWelcomeMail::class => 'Newsletter welcome',
        \App\Mail\CartReminderMail::class => 'Cart reminder',
        \App\Mail\Admin\LowStockDigestMail::class => 'Low-stock digest',
        \App\Mail\ForgotPasswordMail::class => 'Password reset (customer)',
        \App\Mail\AdminResetPassword::class => 'Password reset (staff)',
        \App\Mail\SendUsMail::class => 'Contact form message',
        \App\Notifications\VerifyEmailInBackground::class => 'Confirm email address',
    ];

    private const LOGIN = 'The mail server refused the login (check the mail username and password)';
    private const CONNECT = 'Couldn\'t connect to the mail server (check the mail host and port)';
    private const REJECTED = 'The email address was rejected';
    private const SLOW = 'The mail server took too long to answer';
    private const LIMITED = 'The mail server limited how many emails can be sent (try again later)';
    private const DELETED = 'The order or customer was deleted before the email was sent';

    private static function usesDatabase(): bool
    {
        return config('queue.default') === 'database';
    }

    /** Emails waiting longer than STUCK_MINUTES to be picked up. */
    public static function stuck(): int
    {
        if (!self::usesDatabase()) {
            return 0;
        }

        return DB::table(config('queue.connections.database.table', 'jobs'))
            ->where('queue', self::QUEUE)
            ->whereNull('reserved_at')
            ->where('available_at', '<=', now()->subMinutes(self::STUCK_MINUTES)->getTimestamp())
            ->count();
    }

    public static function waiting(): int
    {
        return self::usesDatabase()
            ? DB::table(config('queue.connections.database.table', 'jobs'))->where('queue', self::QUEUE)->count()
            : 0;
    }

    public static function failedCount(): int
    {
        return DB::table(config('queue.failed.table', 'failed_jobs'))->where('queue', self::QUEUE)->count();
    }

    /** Failed emails, newest first, described for staff. */
    public static function failed(): Collection
    {
        return DB::table(config('queue.failed.table', 'failed_jobs'))
            ->where('queue', self::QUEUE)
            ->orderByDesc('failed_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => self::describe($row));
    }

    public static function describe($row): object
    {
        $payload = json_decode($row->payload, true) ?: [];
        $class = $payload['displayName'] ?? '';
        $command = (string) ($payload['data']['command'] ?? '');

        // Read who it was for and which order straight from the stored job, without rebuilding it
        // (rebuilding fails when the order was deleted, which is often why it failed).
        preg_match_all('/"address";s:\d+:"([^"]+)"/', $command, $addresses);
        $to = array_values(array_unique($addresses[1] ?? []));
        if (!$to && preg_match('/"App\\\\Models\\\\User";s:2:"id";i:(\d+);/', $command, $m)) {
            $to = array_values(array_filter([optional(User::withTrashed()->find((int) $m[1]))->email]));
        }

        $order = null;
        if (preg_match('/"App\\\\Models\\\\Order";s:2:"id";i:(\d+);/', $command, $m)) {
            $order = Order::withTrashed()->find((int) $m[1]);
        }

        [$reason, $detail] = self::reason((string) $row->exception);

        return (object) [
            'id' => $row->id,
            'uuid' => $row->uuid ?? null,
            'type' => self::LABELS[$class] ?? class_basename($class ?: 'Email'),
            'to' => $to,
            'order' => $order,
            'failed_at' => Carbon::parse($row->failed_at),
            'reason' => $reason,
            'detail' => $detail,
        ];
    }

    /** A plain-words reason, plus the first line of the technical error. */
    public static function reason(string $exception): array
    {
        // The first line is "ExceptionClass: message"; the stack trace below it is ignored.
        $first = trim(strtok($exception, "\n") ?: '');
        $text = strtolower($first);

        $rules = [
            self::DELETED => ['no query results for model', 'modelnotfoundexception'],
            self::LOGIN => ['failed to authenticate', 'authentication failed', 'username and password not accepted'],
            self::CONNECT => ['connection could not be established', 'connection refused', 'getaddrinfo', 'no such host', 'network is unreachable'],
            self::REJECTED => ['address rejected', 'does not comply with rfc', 'mailbox unavailable', 'invalid address'],
            self::SLOW => ['timed out', 'timeout', 'has been attempted too many times', 'maxattemptsexceeded'],
            self::LIMITED => ['too many emails', 'rate limit', 'too many messages'],
        ];
        foreach ($rules as $reason => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    return [$reason, $first];
                }
            }
        }

        // SMTP reply codes, as whole numbers only.
        $byCode = ['535' => self::LOGIN, '550' => self::REJECTED, '553' => self::REJECTED, '554' => self::REJECTED,
            '421' => self::LIMITED, '450' => self::LIMITED, '451' => self::LIMITED, '452' => self::LIMITED];
        if (preg_match('/\b(421|450|451|452|535|550|553|554)\b/', $text, $m)) {
            return [$byCode[$m[1]], $first];
        }

        return ['Sending failed', $first];
    }
}
