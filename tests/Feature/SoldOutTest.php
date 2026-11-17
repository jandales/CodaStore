<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoldOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sold_out_product_page_has_no_add_to_bag_button()
    {
        $product = $this->createProduct(0);

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee('Sold out')
            ->assertSee('This item is out of stock right now.')
            ->assertDontSee('id="cart-button"', false);
    }

    public function test_low_stock_note()
    {
        $product = $this->createProduct(3);

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee('Only 3 left')
            ->assertSee('id="cart-button"', false);
    }

    public function test_product_card_shows_sold_out_badge()
    {
        $this->createProduct(0, true, ['name' => 'gone tee']);

        $this->get('/shop')->assertOk()->assertSee('Sold out');
    }

    public function test_adding_a_sold_out_item_is_refused()
    {
        $product = $this->createProduct(0);

        $this->withCredentials()->withCookie('cart-id', '888888888')
            ->postJson(route('cart.store', [$product->encryptedId()]), ['qty' => 1, 'properties' => [['name' => 'size', 'value' => 'M']]])
            ->assertStatus(422)
            ->assertJson(['message' => 'This item is sold out.']);
    }
}
