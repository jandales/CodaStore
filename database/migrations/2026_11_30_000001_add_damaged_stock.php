<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Damaged stock: units kept apart from the sellable (available) stock until they are repaired
 * or written off. On hand = available (qty) + damaged.
 */
class AddDamagedStock extends Migration
{
    public function up()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->unsignedInteger('damaged_qty')->default(0);
        });
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedInteger('damaged_qty')->default(0);
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('damaged_change')->default(0);
            $table->integer('damaged_balance')->default(0);
        });
    }

    public function down()
    {
        Schema::table('product_skus', fn (Blueprint $table) => $table->dropColumn('damaged_qty'));
        Schema::table('stocks', fn (Blueprint $table) => $table->dropColumn('damaged_qty'));
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['damaged_change', 'damaged_balance']);
        });
    }
}
