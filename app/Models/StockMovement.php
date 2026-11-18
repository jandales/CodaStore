<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One change to a product's or variant's stock (Admin > Inventory > Stock details). */
class StockMovement extends Model
{
    public const UPDATED_AT = null;

    /** reason => [label, icon] */
    public const REASONS = [
        'opening' => ['Opening stock', 'fa-flag'],
        'created' => ['Product added', 'fa-plus'],
        'sale' => ['Sold', 'fa-bag-shopping'],
        'cancel' => ['Order cancelled, restocked', 'fa-rotate-left'],
        'return' => ['Returned, restocked', 'fa-rotate-left'],
        'not_collected' => ['COD not collected, restocked', 'fa-rotate-left'],
        'stock_in' => ['Stock added', 'fa-arrow-down'],
        'stock_out' => ['Stock removed', 'fa-arrow-up'],
        'product_edit' => ['Changed on the product page', 'fa-pen'],
        'import' => ['CSV import', 'fa-file-csv'],
        'variant_removed' => ['Variant removed', 'fa-trash'],
    ];

    protected $fillable = ['product_id', 'product_sku_id', 'variant_label', 'change', 'balance', 'reason', 'order_id', 'admin_id', 'note', 'created_at'];

    protected $casts = [
        'change' => 'integer',
        'balance' => 'integer',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason][0] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }

    public function reasonIcon(): string
    {
        return self::REASONS[$this->reason][1] ?? 'fa-circle';
    }
}
