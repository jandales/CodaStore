<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google account ids are 21-digit numbers (about 1e20), larger than a BIGINT can hold
 * (about 9.2e18), so saving a Google user failed. Store provider ids as text.
 */
class WidenUsersProviderId extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_id', 64)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('provider_id')->nullable()->change();
        });
    }
}
