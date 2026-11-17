<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Explicit "on sale" switch. Every product already has a sale_price (the admin form required it),
 * so a lower sale_price alone can't mean "on sale"; existing products stay at their regular price.
 */
class AddOnSaleToProducts extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('on_sale')->default(false)->after('regular_price');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('on_sale');
        });
    }
}
