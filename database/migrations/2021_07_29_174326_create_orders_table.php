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
            $table->integer('shipping_method_id')->unsigned(); 
            $table->integer('shipping_charge')->unsigned();
            $table->integer('num_items_sold');
            $table->double('tax_total');
            $table->double('gross_total');
            $table->double('net_total');
            $table->integer('coupin_id');
            $table->double('coupon_amount');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('status')->nullable();           
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
