<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OrderProduct;
use App\Models\ShippingMethod;
use App\Services\PlaceOrderServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalePriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_is_only_on_sale_when_switched_on_with_a_lower_price()
    {
        $off = $this->createProduct(5, false, ['regular_price' => 500, 'sale_price' => 399, 'on_sale' => false]);
        $on = $this->createProduct(5, false, ['regular_price' => 500, 'sale_price' => 399, 'on_sale' => true]);
        $notLower = $this->createProduct(5, false, ['regular_price' => 500, 'sale_price' => 600, 'on_sale' => true]);

        $this->assertSame(500.0, $off->effectivePrice());
        $this->assertSame(399.0, $on->effectivePrice());
        $this->assertSame(20, $on->discountPercent());
        $this->assertSame(500.0, $notLower->effectivePrice());
        $this->assertFalse($notLower->isOnSale());
    }

    public function test_product_card_and_page_show_the_sale_price()
    {
        $product = $this->createProduct(5, true, ['name' => 'linen shirt', 'regular_price' => 1000, 'sale_price' => 750, 'on_sale' => true]);

        $this->get('/shop')->assertOk()->assertSee('Sale')->assertSee('₱750.00', false)->assertSee('₱1,000.00', false);
        $this->get(route('shop.product', [$product->encryptedId()]))->assertOk()->assertSee('−25%', false);
    }

    public function test_cart_and_order_charge_the_sale_price()
    {
        $this->signInCustomer();
        $product = $this->createProduct(10, false, ['regular_price' => 1000, 'sale_price' => 800, 'on_sale' => true]);
        $cart = Cart::factory()->create(['cart_id' => 777777777]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 2]);
        $method = ShippingMethod::factory()->create(['amount' => 150]);
        request()->cookies->set('cart-id', 777777777);
        session([
            'email' => 'b@example.test',
            'shipping_method' => ['id' => $method->id, 'amount' => 150],
            'shipping_address' => ['firstname' => 'A', 'lastname' => 'B', 'street' => 'C', 'city' => 'D', 'phone' => '1', 'country' => 'PH', 'region' => 'NCR', 'zipcode' => '1100'],
        ]);

        $this->assertSame(1750.0, app(PlaceOrderServices::class)->quote()['total']);
        $order = app(PlaceOrderServices::class)->storeOrder(request(), ['provider' => 'paypal', 'reference' => 'X', 'status' => 'paid']);

        $this->assertSame(800.0, (float) OrderProduct::where('order_id', $order->id)->value('price'));
        $this->assertSame(1750.0, (float) $order->gross_total);
    }

    public function test_ending_a_sale_does_not_change_past_orders()
    {
        $product = $this->createProduct(10, false, ['regular_price' => 1000, 'sale_price' => 800, 'on_sale' => true]);
        $line = OrderProduct::factory()->create(['product_id' => $product->id, 'price' => $product->effectivePrice()]);

        $product->update(['on_sale' => false]);

        $this->assertSame(800.0, (float) $line->fresh()->price);
        $this->assertSame(1000.0, $product->fresh()->effectivePrice());
    }

    public function test_side_cart_json_includes_the_effective_price()
    {
        $product = $this->createProduct(5, false, ['regular_price' => 500, 'sale_price' => 450, 'on_sale' => true]);

        $this->assertSame(450.0, $product->toArray()['effective_price']);
    }

    public function test_admin_validation_for_sale_price()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();

        $this->postJson(route('admin.products.store'), ['regular_price' => 500, 'on_sale' => 1])
            ->assertJsonValidationErrors(['sale_price' => 'Enter a sale price, or untick "On sale".']);

        $this->postJson(route('admin.products.store'), ['regular_price' => 500, 'on_sale' => 1, 'sale_price' => 500])
            ->assertJsonValidationErrors(['sale_price' => 'The sale price must be lower than the regular price.']);

        $this->postJson(route('admin.products.store'), ['regular_price' => 500])
            ->assertJsonMissingValidationErrors('sale_price');
    }
}
