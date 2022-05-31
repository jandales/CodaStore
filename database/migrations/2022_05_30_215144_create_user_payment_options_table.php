<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPaymentOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_payment_options', function (Blueprint $table) {
            $table->id(); 
            $table->integer('user_id');          
            $table->string('card_name');
            $table->string('card_number');
            $table->string('card_expire_date');
            $table->string('card_cvc');  
            $table->integer('status');         
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
        Schema::dropIfExists('user_payment_options');
    }
}
