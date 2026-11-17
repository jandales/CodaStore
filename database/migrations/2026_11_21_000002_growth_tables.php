<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase E: email verification, newsletter and bag reminders.
 */
class GrowthTables extends Migration
{
    public function up()
    {
        // Customers who signed up before verification existed are treated as verified,
        // so nobody is blocked from checkout on deploy.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => DB::raw('created_at')]);

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cart_reminders')->default(true);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('reminded_at')->nullable();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('token', 40)->unique();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'reminded_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cart_reminders');
        });
    }
}
