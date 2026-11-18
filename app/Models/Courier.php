<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A courier the store ships with. Orders store the courier's code (orders.courier), so the
 * code never changes once created; name, tracking link, active and order can.
 */
class Courier extends Model
{
    protected $fillable = ['code', 'name', 'tracking_url', 'active', 'sort_order'];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'courier', 'code');
    }

    /** The courier's public tracking page for a tracking number, or null. */
    public function trackingUrlFor(?string $number): ?string
    {
        if (!$this->tracking_url || !$number) {
            return null;
        }

        return str_replace('{number}', rawurlencode($number), $this->tracking_url);
    }

    /** A unique code made from the name, e.g. "J&T Express" -> "jt-express". */
    public static function codeFor(string $name): string
    {
        $base = Str::limit(Str::slug($name), 30, '') ?: 'courier';
        $code = $base;
        $n = 2;
        while (static::where('code', $code)->exists()) {
            $code = $base . '-' . $n++;
        }

        return $code;
    }
}
