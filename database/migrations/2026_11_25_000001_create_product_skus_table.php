<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Variants with their own price, stock and photo: one product_skus row per option combination
 * (e.g. "M" or "M / Black"). The product keeps a stock total (the sum of its variants) and the
 * lowest/highest variant price so lists, filters and sorting stay simple.
 *
 * Existing products with sizes get one variant per size. Their current stock is split evenly as
 * a starting point and flagged for the store to review.
 */
class CreateProductSkusTable extends Migration
{
    public function up()
    {
        Schema::create('product_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('option_key', 191);
            $table->json('options');
            $table->decimal('price', 12, 2)->nullable(); // null = the product's price
            $table->integer('qty')->default(0);
            $table->string('image', 500)->nullable();
            $table->string('sku', 100)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'option_key']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_from', 12, 2)->nullable();
            $table->decimal('price_to', 12, 2)->nullable();
            $table->boolean('variant_stock_reviewed')->default(true);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sku_id')->nullable()->index();
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->unsignedBigInteger('sku_id')->nullable()->index();
        });

        $this->splitExistingStock();
    }

    private function splitExistingStock(): void
    {
        $now = now();
        $attributeNames = DB::table('attributes')->pluck('name', 'id');

        $productIds = DB::table('product_attributes')->distinct()->pluck('product_id');
        foreach ($productIds as $productId) {
            // Values per option, in the order the store added them.
            $options = [];
            $attributeIds = DB::table('product_attributes')->where('product_id', $productId)->orderBy('id')->pluck('attribute_id');
            foreach ($attributeIds as $attributeId) {
                $values = DB::table('product_variants')->where('product_id', $productId)->where('attribute_id', $attributeId)->orderBy('id')->pluck('name')->all();
                $name = strtolower(trim((string) ($attributeNames[$attributeId] ?? 'option')));
                if ($values) {
                    $options[$name] = array_values(array_unique($values));
                }
            }
            if (!$options) {
                continue;
            }

            $combos = [[]];
            foreach ($options as $name => $values) {
                $next = [];
                foreach ($combos as $combo) {
                    foreach ($values as $value) {
                        $next[] = $combo + [$name => $value];
                    }
                }
                $combos = $next;
            }

            $total = max(0, (int) DB::table('stocks')->where('product_id', $productId)->value('qty'));
            $count = count($combos);
            $each = intdiv($total, $count);
            $extra = $total % $count;

            foreach ($combos as $i => $combo) {
                DB::table('product_skus')->insert([
                    'product_id' => $productId,
                    'option_key' => self::key($combo),
                    'options' => json_encode($combo),
                    'price' => null,
                    'qty' => $each + ($i < $extra ? 1 : 0),
                    'position' => $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('products')->where('id', $productId)->update(['variant_stock_reviewed' => false]);
        }

        // Bags in progress: link each line to its variant.
        foreach (DB::table('cart_items')->get(['id', 'product_id', 'attributes']) as $item) {
            $chosen = json_decode((string) $item->attributes, true);
            if (!is_array($chosen) || !$chosen) {
                continue;
            }
            $skuId = DB::table('product_skus')->where('product_id', $item->product_id)->where('option_key', self::key($chosen))->value('id');
            if ($skuId) {
                DB::table('cart_items')->where('id', $item->id)->update(['sku_id' => $skuId]);
            }
        }
    }

    /** Same as ProductSku::keyFor(): "color:black|size:m". */
    private static function key(array $options): string
    {
        $parts = [];
        foreach ($options as $name => $value) {
            $parts[mb_strtolower(trim((string) $name))] = mb_strtolower(trim((string) $value));
        }
        ksort($parts);

        return implode('|', array_map(fn ($k, $v) => $k . ':' . $v, array_keys($parts), $parts));
    }

    public function down()
    {
        Schema::table('order_products', fn (Blueprint $table) => $table->dropColumn('sku_id'));
        Schema::table('cart_items', fn (Blueprint $table) => $table->dropColumn('sku_id'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['price_from', 'price_to', 'variant_stock_reviewed']));
        Schema::dropIfExists('product_skus');
    }
}
