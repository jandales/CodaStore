<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLowStockThresholdToGeneralSettings extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->unsignedInteger('low_stock_threshold')->default(5);
        });
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('low_stock_threshold');
        });
    }
}
