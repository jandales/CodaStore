<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('discount_type')->nullable()->default(0);
            $table->double('amount')->nullable()->default(0);
            $table->double('min_amount')->nullable()->default(0);
            $table->double('max_amount')->nullable()->default(0);             
            $table->integer('limit_per_coupon')->nullable()->default(0);  
            $table->integer('limit_to_xitems')->nullable()->default(0);  
            $table->integer('limit_per_user')->nullable()->default(0);  
            $table->integer('usage')->nullable()->default(0);    
            $table->timestamp('expire_at')->nullable();
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
        Schema::dropIfExists('coupons');
    }
}
