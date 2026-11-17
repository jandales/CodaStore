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
