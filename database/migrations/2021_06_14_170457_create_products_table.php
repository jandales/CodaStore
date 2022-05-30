<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('category_id');
            $table->string('slug');
            $table->string('sku');
            $table->string('barcode');
            $table->longText('short_description');
            $table->longText('long_description');
            $table->integer('sale_price');
            $table->integer('regular_price');
            $table->integer('is_taxeble');
            $table->integer('featured'); 
            $table->string('imagePath');
            $table->string('tags');
            $table->string('status');
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
        Schema::dropIfExists('products');
    }
}
