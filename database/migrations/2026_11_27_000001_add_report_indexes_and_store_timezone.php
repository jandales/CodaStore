<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Sales reports: indexes for date-range queries, and the store timezone set to Asia/Manila if empty. */
class AddReportIndexesAndStoreTimezone extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'orders_created_at_index');
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->index('product_id', 'order_products_product_id_index');
        });

        DB::table('general_settings')->where(fn ($q) => $q->whereNull('timezone')->orWhere('timezone', ''))
            ->update(['timezone' => 'Asia/Manila']);
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_status_created_at_index');
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropIndex('order_products_product_id_index');
        });
    }
}
