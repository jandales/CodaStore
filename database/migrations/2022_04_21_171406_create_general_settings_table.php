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
            $table->string('tag_line');
            $table->string('site_url');
            $table->string('site_email');
            $table->string('timezone');
            $table->string('date_format');
            $table->string('time_format');
            $table->string('campany_name');
            $table->string('campany_address');
            $table->string('campany_city');
            $table->string('campany_region');
            $table->string('campany_county');
            $table->string('campany_zipcode');
            $table->string('campany_phone');
            $table->integer('app_perpage');
            $table->integer('shop_perpage');
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
