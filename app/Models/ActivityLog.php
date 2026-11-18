<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Who changed what in the admin: order status, prices, stock, staff access, imports.
 * Entries are written by the model observers (only for changes made by a signed-in admin)
 * and by a few explicit calls.
 */
class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['admin_id', 'action', 'subject_type', 'subject_id', 'description', 'properties', 'created_at'];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /** Filters on the activity page: key => [label, action prefix]. */
    public const GROUPS = [
        'orders' => ['Orders', 'order.'],
        'products' => ['Products', 'product.'],
        'stock' => ['Stock', 'stock.'],
        'staff' => ['Staff', 'staff.'],
        'couriers' => ['Couriers', 'courier.'],
    ];

    /** Set while a bulk job (e.g. CSV import) writes its own summary entry instead. */
    private static bool $muted = false;

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public static function record(string $action, ?Model $subject, string $description, array $properties = []): ?self
    {
        if (self::$muted) {
            return null;
        }

        return static::create([
            'admin_id' => auth('admin')->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'description' => mb_substr($description, 0, 500),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }

    /** Observers only log changes made by a signed-in admin (not checkout, not the scheduler). */
    public static function byAdmin(): bool
    {
        return !self::$muted && auth('admin')->check();
    }

    /** Run $callback without per-model entries. */
    public static function muted(callable $callback)
    {
        $previous = self::$muted;
        self::$muted = true;
        try {
            return $callback();
        } finally {
            self::$muted = $previous;
        }
    }

    /** Entries about one record, newest first. */
    public function scopeFor($query, Model $subject)
    {
        return $query->where('subject_type', get_class($subject))->where('subject_id', $subject->getKey());
    }

    public function actorName(): string
    {
        if (!$this->admin_id) {
            return 'System';
        }
        if (!$this->admin) {
            return 'Deleted staff member';
        }

        return trim($this->admin->firstname . ' ' . $this->admin->lastname) ?: $this->admin->username;
    }
}
