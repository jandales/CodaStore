<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;
use App\Models\StockMovement;

/**
 * Records stock history. Products with variants get one history per variant; products without
 * variants get one for the product. Each row records the change to the available (sellable)
 * stock and to the damaged stock, with both balances afterwards.
 *
 *  - record():  a known change (sales, restocks), right after the stock was changed.
 *  - snapshot() + recordDiff(): any other change (product page, adjust stock, CSV import):
 *    compare the stock before and after and record each difference.
 */
class StockMovements
{
    public static function record(int $productId, ?int $skuId, int $change, string $reason, array $extra = []): ?StockMovement
    {
        $damagedChange = (int) ($extra['damaged_change'] ?? 0);
        if ($change === 0 && $damagedChange === 0) {
            return null;
        }

        if ($skuId) {
            $sku = ProductSku::find($skuId);
            $balance = (int) optional($sku)->qty;
            $damaged = (int) optional($sku)->damaged_qty;
            $label = $sku ? $sku->label() : ($extra['variant_label'] ?? null);
        } else {
            $stock = Stock::where('product_id', $productId)->first(['qty', 'damaged_qty']);
            $balance = (int) optional($stock)->qty;
            $damaged = (int) optional($stock)->damaged_qty;
            $label = null;
        }

        return StockMovement::create([
            'product_id' => $productId,
            'product_sku_id' => $skuId,
            'variant_label' => $label,
            'change' => $change,
            'balance' => max(0, $balance),
            'damaged_change' => $damagedChange,
            'damaged_balance' => max(0, $damaged),
            'reason' => $reason,
            'order_id' => $extra['order_id'] ?? null,
            'admin_id' => array_key_exists('admin_id', $extra) ? $extra['admin_id'] : self::adminId(),
            'note' => isset($extra['note']) && $extra['note'] !== '' ? mb_substr((string) $extra['note'], 0, 255) : null,
        ]);
    }

    /** Available and damaged stock of a product and each of its variants right now. */
    public static function snapshot(int $productId): array
    {
        $stock = Stock::where('product_id', $productId)->first(['qty', 'damaged_qty']);

        return [
            'product' => (int) optional($stock)->qty,
            'product_damaged' => (int) optional($stock)->damaged_qty,
            'skus' => ProductSku::where('product_id', $productId)->get(['id', 'qty', 'damaged_qty', 'options'])
                ->mapWithKeys(fn ($s) => [$s->id => ['qty' => (int) $s->qty, 'damaged' => (int) $s->damaged_qty, 'label' => $s->label()]])
                ->all(),
        ];
    }

    /** Record what changed since $before. */
    public static function recordDiff(int $productId, array $before, string $reason, array $extra = []): void
    {
        $after = self::snapshot($productId);

        if (!$before['skus'] && !$after['skus']) {
            self::record($productId, null, $after['product'] - $before['product'], $reason, $extra + [
                'damaged_change' => $after['product_damaged'] - ($before['product_damaged'] ?? 0),
            ]);
            return;
        }

        foreach ($after['skus'] as $id => $sku) {
            self::record($productId, (int) $id, $sku['qty'] - ($before['skus'][$id]['qty'] ?? 0), $reason, $extra + [
                'damaged_change' => $sku['damaged'] - ($before['skus'][$id]['damaged'] ?? 0),
            ]);
        }
        foreach ($before['skus'] as $id => $sku) {
            if (!isset($after['skus'][$id]) && ($sku['qty'] !== 0 || ($sku['damaged'] ?? 0) !== 0)) {
                StockMovement::create([
                    'product_id' => $productId, 'product_sku_id' => (int) $id, 'variant_label' => $sku['label'],
                    'change' => -$sku['qty'], 'balance' => 0,
                    'damaged_change' => -($sku['damaged'] ?? 0), 'damaged_balance' => 0,
                    'reason' => 'variant_removed', 'admin_id' => self::adminId(),
                ]);
            }
        }
    }

    /** Run $change and record its effect on the product's stock. */
    public static function track(int $productId, string $reason, callable $change, array $extra = [])
    {
        $before = self::snapshot($productId);
        $result = $change();
        self::recordDiff($productId, $before, $reason, $extra);

        return $result;
    }

    private static function adminId(): ?int
    {
        try {
            return optional(auth('admin')->user())->id;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
