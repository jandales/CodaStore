<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'tag_line',
        'site_url',
        'site_email',
        'timezone',
        'date_format',
        'time_format',
        'low_stock_threshold',
    ];

    public const DEFAULT_LOW_STOCK = 5;

    protected static function booted()
    {
        static::saved(function () {
            app()->forgetInstance('store.low_stock_threshold');
        });
    }

    /** Stock level at or below which a product counts as low on stock. */
    public static function lowStockThreshold(): int
    {
        // Read once per request: product cards ask for it many times.
        if (!app()->bound('store.low_stock_threshold')) {
            $value = optional(static::find(1))->low_stock_threshold;
            app()->instance('store.low_stock_threshold', $value === null ? self::DEFAULT_LOW_STOCK : (int) $value);
        }

        return app('store.low_stock_threshold');
    }

    /** Where store notifications go: the store email from Settings, or every administrator. */
    public static function storeRecipients(): array
    {
        $storeEmail = optional(static::find(1))->site_email;
        if ($storeEmail) {
            return [$storeEmail];
        }

        return Admin::where('role', 1)->pluck('email')->filter()->values()->all();
    }
}
