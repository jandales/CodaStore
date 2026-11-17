<?php

namespace Tests\Feature;

use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\Stock;
use App\Services\PlaceOrderServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderStockTest extends TestCase
{
    use RefreshDatabase;

    /** Put items in a bag identified by the cart-id cookie and fill in the checkout session. */
    private function prepareCheckout(array $lines, int $cartId = 111111111): Cart
    {
        $cart = Cart::factory()->create(['cart_id' => $cartId]);
        $total = 0;
        foreach ($lines as [$product, $qty]) {
            CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => $qty, 'attributes' => ['size' => 'M']]);
            $total += $qty * $product->regular_price;
        }
        $cart->forceFill(['total' => $total])->save();

        $method = ShippingMethod::factory()->create();
        request()->cookies->set('cart-id', $cartId);
        session([
            'email' => 'buyer@example.test',
            'shipping_method' => ['id' => $method->id, 'amount' => $method->amount],
            'shipping_address' => [
                'firstname' => 'Test', 'lastname' => 'Buyer', 'street' => '1 Main St', 'city' => 'Quezon City',
                'phone' => '09170000000', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100',
            ],
        ]);

        return $cart;
    }

    private function placeOrder(): Order
    {
        return app(PlaceOrderServices::class)->storeOrder(request(), [
            'provider' => 'paypal', 'reference' => 'TEST-CAPTURE-1', 'status' => 'paid',
        ]);
    }

    private function stockOf($product): int
    {
        return (int) Stock::where('product_id', $product->id)->value('qty');
    }

    public function test_order_takes_stock_once()
    {
        $this->signInCustomer();
        $product = $this->createProduct(5);
        $this->prepareCheckout([[$product, 2]]);

        $order = $this->placeOrder();

        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertSame(3, $this->stockOf($product));
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'provider' => 'paypal', 'reference_number' => 'TEST-CAPTURE-1', 'status' => 'paid']);
    }

    public function test_order_fails_when_not_enough_stock_and_nothing_is_saved()
    {
        $this->signInCustomer();
        $product = $this->createProduct(1, true, ['name' => 'overshirt']);
        $this->prepareCheckout([[$product, 2]]);

        try {
            $this->placeOrder();
            $this->fail('Expected OutOfStockException');
        } catch (OutOfStockException $e) {
            $this->assertSame('Only 1 left of Overshirt. Please update your bag.', $e->getMessage());
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $this->stockOf($product));
        $this->assertSame(1, CartItem::count(), 'the bag is kept so the shopper can fix it');
    }

    public function test_sold_out_message()
    {
        $this->signInCustomer();
        $product = $this->createProduct(0, true, ['name' => 'tee']);
        $this->prepareCheckout([[$product, 1]]);

        $this->expectException(OutOfStockException::class);
        $this->expectExceptionMessage('Tee is sold out. Please remove it from your bag.');
        $this->placeOrder();
    }

    public function test_same_product_in_two_sizes_is_checked_as_a_total()
    {
        $this->signInCustomer();
        $product = $this->createProduct(3);
        $this->prepareCheckout([[$product, 2], [$product, 2]]); // e.g. 2 x M and 2 x L

        $this->expectException(OutOfStockException::class);
        $this->placeOrder();
    }

    public function test_one_short_item_rolls_back_the_whole_order()
    {
        $this->signInCustomer();
        $plenty = $this->createProduct(10);
        $short = $this->createProduct(0);
        $this->prepareCheckout([[$plenty, 1], [$short, 1]]);

        try {
            $this->placeOrder();
        } catch (OutOfStockException $e) {
        }

        $this->assertSame(10, $this->stockOf($plenty), 'stock of the other item is untouched');
        $this->assertSame(0, Order::count());
    }

    public function test_empty_bag_is_rejected()
    {
        $this->signInCustomer();
        Cart::factory()->create(['cart_id' => 222222222]);
        request()->cookies->set('cart-id', 222222222);

        $this->expectExceptionMessage('Your bag is empty.');
        $this->placeOrder();
    }

    public function test_bag_page_warns_when_quantity_is_above_stock()
    {
        $product = $this->createProduct(2);
        $this->prepareCheckout([[$product, 5]], 333333333);

        $this->withCookie('cart-id', '333333333')->get('/cart')
            ->assertOk()
            ->assertSee('Only 2 left. Please lower the quantity.');
    }
}
