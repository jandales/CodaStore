<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cash on delivery: store settings, a payment-method fee on orders, and when the cash was
 * collected (or refunded by hand) on the payment.
 */
class AddCashOnDelivery extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('cod_enabled')->default(true);
            $table->decimal('cod_fee', 10, 2)->default(0);
            $table->decimal('cod_max_total', 12, 2)->nullable()->default(5000);
            $table->json('cod_shipping_methods')->nullable(); // null = every shipping method
            $table->unsignedSmallInteger('cod_max_open_orders')->default(2);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('payment_fee', 10, 2)->default(0);
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn(['collected_at', 'refunded_at']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_fee');
        });
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['cod_enabled', 'cod_fee', 'cod_max_total', 'cod_shipping_methods', 'cod_max_open_orders']);
        });
    }
}
