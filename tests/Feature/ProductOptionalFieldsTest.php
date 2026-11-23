<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** SKU, barcode and regular price are optional on the product form. */
class ProductOptionalFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_be_created_without_sku_barcode_or_price()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();

        $this->postJson(route('admin.products.store'), [
            'name' => 'plain tee', 'category_id' => \App\Models\Category::first()->id,
            'sku' => '', 'barcode' => '', 'regular_price' => '', 'status' => 0, 'qty' => 2,
        ])->assertOk()->assertJsonMissingValidationErrors(['sku', 'barcode', 'regular_price']);

        $product = Product::where('name', 'plain tee')->firstOrFail();
        $this->assertNull($product->sku);
        $this->assertNull($product->barcode);
        $this->assertSame(0, (int) $product->regular_price);
    }

    public function test_clearing_them_when_editing_works_too()
    {
        $product = $this->createProduct(5, false, ['sku' => 'TEE-1', 'barcode' => '123', 'regular_price' => 500]);
        $this->signInAdmin();

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), [
            'name' => $product->name, 'category_id' => $product->category_id,
            'sku' => '', 'barcode' => '', 'regular_price' => '', 'status' => 1, 'qty' => 5,
        ])->assertOk();

        $product->refresh();
        $this->assertNull($product->sku);
        $this->assertNull($product->barcode);
        $this->assertSame(0, (int) $product->regular_price);
    }

    public function test_the_values_are_still_checked_when_given()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();

        $this->postJson(route('admin.products.store'), [
            'name' => 'plain tee', 'category_id' => \App\Models\Category::first()->id,
            'regular_price' => -5, 'status' => 0, 'qty' => 2,
        ])->assertStatus(422)->assertJsonValidationErrors(['regular_price']);
    }
}
