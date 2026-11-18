<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Inventory > Adjust stock: Stock In, Stock Out and Damage (mark as damaged, repaired, write off)
 * for one variant, or for a product without variants. Every adjustment lands in the stock history.
 */
class StockAdjuster
{
    /** action => [label, reason in the stock history] */
    public const ACTIONS = [
        'stock_in' => ['Stock In', 'stock_in'],
        'stock_out' => ['Stock Out', 'stock_out'],
        'damage' => ['Mark as damaged', 'damaged'],
        'repaired' => ['Repaired, back to stock', 'repaired'],
        'write_off' => ['Write off damaged', 'written_off'],
    ];

    /** @return array{available: int, damaged: int, on_hand: int} */
    public function adjust(Product $product, ?int $skuId, string $action, int $qty, ?string $note = null): array
    {
        if (!isset(self::ACTIONS[$action])) {
            throw ValidationException::withMessages(['action' => 'Choose what happened to the stock.']);
        }
        if ($qty < 1) {
            throw ValidationException::withMessages(['qty' => 'Enter a quantity of at least 1.']);
        }

        $hasVariants = $product->skus()->exists();
        if ($hasVariants && !$skuId) {
            throw ValidationException::withMessages(['sku_id' => 'Choose a variant.']);
        }

        return DB::transaction(function () use ($product, $skuId, $action, $qty, $note, $hasVariants) {
            return StockMovements::track($product->id, self::ACTIONS[$action][1], function () use ($product, $skuId, $action, $qty, $hasVariants) {
                $row = $hasVariants
                    ? ProductSku::where('product_id', $product->id)->whereKey($skuId)->lockForUpdate()->first()
                    : (Stock::where('product_id', $product->id)->lockForUpdate()->first() ?: Stock::create(['product_id' => $product->id, 'qty' => 0]));
                if (!$row) {
                    throw ValidationException::withMessages(['sku_id' => 'That variant no longer exists.']);
                }

                $available = max(0, (int) $row->qty);
                $damaged = max(0, (int) $row->damaged_qty);
                $notEnough = fn (int $have, string $what) => ValidationException::withMessages(['qty' => "Only {$have} {$what}."]);

                switch ($action) {
                    case 'stock_in':
                        $available += $qty;
                        break;
                    case 'stock_out':
                        if ($qty > $available) throw $notEnough($available, 'available');
                        $available -= $qty;
                        break;
                    case 'damage':
                        if ($qty > $available) throw $notEnough($available, 'available to mark as damaged');
                        $available -= $qty;
                        $damaged += $qty;
                        break;
                    case 'repaired':
                        if ($qty > $damaged) throw $notEnough($damaged, 'damaged');
                        $damaged -= $qty;
                        $available += $qty;
                        break;
                    case 'write_off':
                        if ($qty > $damaged) throw $notEnough($damaged, 'damaged to write off');
                        $damaged -= $qty;
                        break;
                }

                $row->qty = $available;
                $row->damaged_qty = $damaged;
                $row->save();
                if ($hasVariants) {
                    ProductSkus::refreshTotals($product);
                }

                return ['available' => $available, 'damaged' => $damaged, 'on_hand' => $available + $damaged];
            }, ['note' => $note]);
        });
    }
}
