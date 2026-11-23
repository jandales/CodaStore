<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Each variant can have its own barcode, next to its own SKU. */
class AddBarcodeToProductSkus extends Migration
{
    public function up()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->after('sku');
        });
    }

    public function down()
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
}
