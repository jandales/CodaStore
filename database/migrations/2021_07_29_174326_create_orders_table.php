<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();          
            $table->integer('user_id')->unsigned(); 
            $table->bigInteger('order_number');
            $table->integer('shipping_method_id')->unsigned(); 
            $table->integer('shipping_charge')->unsigned();
            $table->integer('num_items_sold')->default(0);
            $table->double('tax_total')->default(0);
            $table->double('gross_total')->default(0);
            $table->double('net_total')->default(0);
            $table->integer('coupon_id')->nullable();
            $table->double('coupon_amount')->default(0);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('status')->nullable();  
            $table->softDeletes();          
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
