<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserShippingAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shipping_address', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();     
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('zipcode')->nullable();
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
        Schema::dropIfExists('user_shipping_address');
    }
}
