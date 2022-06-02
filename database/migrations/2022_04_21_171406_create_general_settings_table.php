<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('tag_line')->nullable();;
            $table->string('site_url');
            $table->string('site_email')->nullable();;
            $table->string('timezone')->nullable();;
            $table->string('date_format')->nullable();;
            $table->string('time_format')->nullable();;
            $table->string('campany_name')->nullable();;
            $table->string('campany_address')->nullable();;
            $table->string('campany_city')->nullable();;
            $table->string('campany_region')->nullable();;
            $table->string('campany_county')->nullable();;
            $table->string('campany_zipcode')->nullable();;
            $table->string('campany_phone')->nullable();;
            $table->integer('app_perpage')->nullable();;
            $table->integer('shop_perpage')->nullable();;
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
        Schema::dropIfExists('general_settings');
    }
}
