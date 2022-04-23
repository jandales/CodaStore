<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckoutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkout', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('shipping_method')->default(1);
            $table->text('email');
            $table->string('shipping_firstname');
            $table->string('shipping_lastname');
            $table->string('shipping_street');
            $table->string('shipping_city');
            $table->string('shipping_phone');
            $table->string('shipping_country');
            $table->string('shipping_region');
            $table->string('shipping_zipcode');             
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
        Schema::dropIfExists('checkout');
    }
}
