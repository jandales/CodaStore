<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('campany_name');
            $table->string('campany_address');
            $table->string('campany_city');
            $table->string('campany_region');
            $table->string('campany_county');
            $table->string('campany_zipcode');
            $table->string('campany_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('campany_name');
            $table->string('campany_address');
            $table->string('campany_city');
            $table->string('campany_region');
            $table->string('campany_county');
            $table->string('campany_zipcode');
            $table->string('campany_phone');
        });
    }
}
