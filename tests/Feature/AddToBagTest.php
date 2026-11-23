<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToBagTest extends TestCase
{
    use RefreshDatabase;

    private function addToBag($product, array $data)
    {
        // The storefront identifies the bag by a "cart-id" cookie set on first visit.
        return $this->withCredentials()->withCookie('cart-id', '123456789')
            ->postJson(route('cart.store', [$product->encryptedId()]), $data);
    }

    public function test_product_with_sizes_requires_a_size()
    {
        $product = $this->createProduct();

        $this->addToBag($product, ['qty' => 1])
            ->assertStatus(422)
            ->assertJsonPath('errors.properties.0', 'Please select size');
    }

    public function test_empty_size_value_is_rejected_with_the_option_name()
    {
        $product = $this->createProduct();

        $response = $this->addToBag($product, ['qty' => 1, 'properties' => [['name' => 'size', 'value' => '']]]);

        $response->assertStatus(422);
        $this->assertSame('Please select size', $response->json('errors')['properties.0.value'][0]);
    }

    /** A Size attribute attached to the product, but no sizes added. */
    private function productWithEmptySizeAttribute()
    {
        $product = $this->createProduct(10, false);
        $size = \App\Models\Attribute::where('name', 'size')->first();
        \App\Models\ProductAttribute::factory()->create(['product_id' => $product->id, 'attribute_id' => $size->id]);

        return $product->fresh();
    }

    public function test_product_without_sizes_can_be_added_even_if_an_empty_size_is_sent()
    {
        $product = $this->productWithEmptySizeAttribute();

        // What the product page used to send for an attribute without options.
        $this->addToBag($product, ['qty' => 1, 'properties' => [['name' => 'size', 'value' => '']]])
            ->assertOk();

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'qty' => 1]);
    }

    public function test_product_page_hides_the_size_label_when_there_are_no_sizes()
    {
        $product = $this->productWithEmptySizeAttribute();

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertDontSee('Size :', false)
            ->assertDontSee('pdp-variants', false);

        $this->getJson(route('product.hasvariants', [$product->encryptedId()]))
            ->assertExactJson(['attributes' => [], 'hasvariant' => false]);
    }

    public function test_product_page_shows_the_size_label_when_there_are_sizes()
    {
        $product = $this->createProduct();

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee('pdp-variants', false);
    }

    public function test_quantity_must_be_at_least_one()
    {
        $product = $this->createProduct();

        $this->addToBag($product, ['qty' => 0, 'properties' => [['name' => 'size', 'value' => 'M']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('qty');
    }

    public function test_valid_add_to_bag_succeeds()
    {
        $product = $this->createProduct();

        $this->addToBag($product, ['qty' => 2, 'properties' => [['name' => 'size', 'value' => 'M']]])
            ->assertOk()
            ->assertJsonStructure(['count']);

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'qty' => 2]);
    }
}
