<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderProduct;
use App\Models\ShippingMethod;
use App\Services\PlaceOrderServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * All money maths goes through Product::effectivePrice(). Products that are not on sale
 * (the default) pay the regular price; see SalePriceTest for sales.
 */
class PricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_effective_price_is_the_regular_price_when_not_on_sale()
    {
        $product = $this->createProduct(10, false, ['regular_price' => 899, 'sale_price' => 499]);

        $this->assertSame(899.0, $product->effectivePrice());
    }

    public function test_cart_total_is_recalculated_from_current_prices()
    {
        $a = $this->createProduct(10, false, ['regular_price' => 500]);
        $b = $this->createProduct(10, false, ['regular_price' => 1200]);
        $cart = Cart::factory()->create();
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $a->id, 'qty' => 2]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $b->id, 'qty' => 1]);
        $cart->forceFill(['total' => 1])->save(); // stale stored value

        $this->assertSame(2200.0, (float) $cart->fresh()->recalculateTotal()->total);
    }

    public function test_order_uses_current_price_even_if_the_saved_bag_total_is_stale()
    {
        $this->signInCustomer();
        $product = $this->createProduct(10, false, ['regular_price' => 750]);
        $cart = Cart::factory()->create(['cart_id' => 444444444]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 2]);
        $cart->forceFill(['total' => 100])->save(); // price changed after the item was added

        $method = ShippingMethod::factory()->create();
        request()->cookies->set('cart-id', 444444444);
        session([
            'email' => 'buyer@example.test',
            'shipping_method' => ['id' => $method->id, 'amount' => $method->amount],
            'shipping_address' => ['firstname' => 'A', 'lastname' => 'B', 'street' => 'C', 'city' => 'D', 'phone' => '1', 'country' => 'PH', 'region' => 'NCR', 'zipcode' => '1100'],
        ]);

        $order = app(PlaceOrderServices::class)->storeOrder(request(), ['provider' => 'paypal', 'reference' => 'X', 'status' => 'paid']);

        $this->assertSame(1500.0, (float) $order->net_total);
        $this->assertSame(750.0, (float) OrderProduct::where('order_id', $order->id)->value('price'));
    }
}
