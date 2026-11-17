<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The column was created as "campany_county" (typo) while the settings form and service use
 * "campany_country", so saving the company address failed. Rename it to match the code.
 */
class RenameCampanyCountyColumn extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('general_settings', 'campany_county') && !Schema::hasColumn('general_settings', 'campany_country')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('campany_county', 'campany_country');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('general_settings', 'campany_country')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->renameColumn('campany_country', 'campany_county');
            });
        }
    }
}
