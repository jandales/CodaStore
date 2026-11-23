<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media order on the product form: photos are shown in sort_order (then upload order). The first
 * photo is the product's main image. Existing photos keep 0, so they stay in upload order.
 */
class AddSortOrderToPhotos extends Migration
{
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down()
    {
        // Drop the index before the column (needed for native column drops on SQLite).
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'sort_order']);
        });
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
