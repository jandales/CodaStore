<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock history: one row per change, per variant (or per product when it has no variants).
 * Starts with an "opening stock" row for what is in stock today.
 */
class CreateStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sku_id')->nullable(); // kept when the variant is later removed
            $table->string('variant_label', 191)->nullable();          // e.g. "M / Black", readable after removal
            $table->integer('change');                                 // +5 in, -2 out
            $table->integer('balance');                                // stock right after this change
            $table->string('reason', 30);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
            $table->index(['product_sku_id', 'created_at']);
            $table->index('order_id');
        });

        $now = now();
        $rows = [];
        $withVariants = DB::table('product_skus')->distinct()->pluck('product_id')->flip();

        foreach (DB::table('product_skus')->orderBy('product_id')->orderBy('position')->get() as $sku) {
            $options = json_decode($sku->options ?? '[]', true) ?: [];
            $rows[] = [
                'product_id' => $sku->product_id, 'product_sku_id' => $sku->id,
                'variant_label' => implode(' / ', array_map('strval', array_values($options))),
                'change' => max(0, (int) $sku->qty), 'balance' => max(0, (int) $sku->qty),
                'reason' => 'opening', 'created_at' => $now,
            ];
        }
        foreach (DB::table('stocks')->get() as $stock) {
            if (!isset($withVariants[$stock->product_id])) {
                $rows[] = [
                    'product_id' => $stock->product_id, 'product_sku_id' => null, 'variant_label' => null,
                    'change' => max(0, (int) $stock->qty), 'balance' => max(0, (int) $stock->qty),
                    'reason' => 'opening', 'created_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('stock_movements')->insert($chunk);
        }
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
