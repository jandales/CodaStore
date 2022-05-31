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
            $table->string('firstname');
            $table->string('lastname');      
            $table->string('street');
            $table->string('city');
            $table->string('phone');
            $table->string('country');
            $table->string('region');
            $table->string('zipcode');
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
