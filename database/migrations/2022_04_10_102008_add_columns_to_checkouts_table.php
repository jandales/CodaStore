<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCheckoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->string('billing_firstname')->after('shipping_zipcode');
            $table->string('billing_lastname');
            $table->string('billing_street');
            $table->string('billing_city');
            $table->string('billing_phone');
            $table->string('billing_country');
            $table->string('billing_region');
            $table->string('billing_zipcode'); 

            $table->string('card_name');
            $table->string('card_number');
            $table->string('card_expire_date');
            $table->string('card_cvc'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checkouts', function (Blueprint $table) {
            $table->string('billing_firstname')->after('shipping_zipcode');
            $table->string('billing_lastname');
            $table->string('billing_street');
            $table->string('billing_city');
            $table->string('billing_phone');
            $table->string('billing_country');
            $table->string('billing_region');
            $table->string('billing_zipcode'); 

            $table->string('card_name');
            $table->string('card_name');
            $table->string('card_expire_date');
            $table->string('card_cvc'); 
        });
    }
}
