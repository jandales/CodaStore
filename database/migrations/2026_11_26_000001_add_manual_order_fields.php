<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual orders: where an order came from (website, Facebook, phone…), who created it in the
 * admin, an order discount, and the payment method for cash / bank transfer / GCash payments.
 * Guest orders (no customer account) make orders.user_id optional.
 */
class AddManualOrderFields extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('channel', 20)->default('website')->index();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->string('method', 30)->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_payments', fn (Blueprint $table) => $table->dropColumn('method'));
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['channel', 'created_by_admin_id', 'discount_amount']);
        });
    }
}
