<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\Products\ProductSkus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class VariantShoppingTest extends TestCase
{
    use RefreshDatabase;

    private $product;
    private $method;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
        $this->signInCustomer();

        // S: 2 left, M: 1 left at the product price (500), XL: 3 left at 700.
        $this->product = $this->createProduct(0, true, ['name' => 'overshirt', 'regular_price' => 500]);
        app(ProductSkus::class)->sync($this->product, [
            ['options' => ['size' => 'S'], 'qty' => 2],
            ['options' => ['size' => 'M'], 'qty' => 1],
            ['options' => ['size' => 'L'], 'qty' => 0],
            ['options' => ['size' => 'XL'], 'price' => 700, 'qty' => 3],
        ]);
        $this->product = $this->product->fresh();
        $this->method = ShippingMethod::factory()->create(['amount' => 150]);
    }

    private function add(string $size, int $qty = 1)
    {
        return $this->withCredentials()->withCookie('cart-id', '123456789')
            ->postJson(route('cart.store', [$this->product->encryptedId()]), ['qty' => $qty, 'properties' => [['name' => 'size', 'value' => $size]]]);
    }

    private function sku(string $size)
    {
        return $this->product->skuFor(['size' => $size])->fresh();
    }

    private function checkoutSession(): void
    {
        $this->withCookie('cart-id', '123456789')->withSession([
            'email' => 'buyer@example.test',
            'shipping_method' => ['id' => $this->method->id, 'name' => $this->method->name, 'amount' => 150],
            'shipping_address' => ['firstname' => 'T', 'lastname' => 'B', 'street' => '1', 'city' => 'QC', 'phone' => '0917', 'country' => 'PH', 'region' => 'NCR', 'zipcode' => '1100'],
        ]);
    }

    public function test_each_size_is_its_own_bag_line_with_its_own_price()
    {
        $this->add('M')->assertOk();
        $this->add('XL', 2)->assertOk();

        $lines = CartItem::orderBy('id')->get();
        $this->assertCount(2, $lines, 'an M and an XL are two lines, not merged');
        $this->assertSame([$this->sku('M')->id, $this->sku('XL')->id], $lines->pluck('sku_id')->map(fn ($v) => (int) $v)->all());
        $this->assertSame(500.0, $lines[0]->unitPrice());
        $this->assertSame(700.0, $lines[1]->unitPrice());
        $this->assertSame(1900.0, (float) Cart::first()->total, '500 + 2 x 700');
    }

    public function test_sold_out_or_unknown_sizes_cannot_be_added()
    {
        $this->add('L')->assertStatus(422)->assertJson(['message' => 'L is sold out. Please choose another option.']);
        $this->add('XXL')->assertStatus(422)->assertJson(['message' => "That option isn't available. Please choose another."]);
        $this->assertSame(0, CartItem::count());
    }

    public function test_cannot_put_more_of_a_size_in_the_bag_than_it_has()
    {
        $this->add('S', 5)->assertOk();
        $this->assertSame(2, (int) CartItem::first()->qty, 'capped at the 2 S left');

        $this->add('S')->assertStatus(422)->assertJson(['message' => 'You already have all 2 in your bag.']);
    }

    public function test_placing_an_order_takes_stock_from_the_variant_and_records_it()
    {
        $this->add('XL', 2);
        $this->checkoutSession();

        $this->post(route('checkout.place'), ['payment_method' => 'cod'])->assertRedirect(route('checkout.completed'));

        $order = Order::first();
        $line = $order->items->first();
        $this->assertSame($this->sku('XL')->id, (int) $line->sku_id);
        $this->assertSame(700.0, (float) $line->price);
        $this->assertSame(1550.0, (float) $order->gross_total, '2 x 700 + 150 shipping');
        $this->assertSame(1, $this->sku('XL')->qty);
        $this->assertSame(4, $this->product->fresh()->stockQty(), 'product total follows: 6 - 2');
        $this->assertSame(2, $this->sku('S')->qty, 'other sizes untouched');
    }

    public function test_the_last_unit_of_a_size_cannot_be_sold_twice()
    {
        $this->add('M');
        $this->sku('M')->update(['qty' => 0]); // someone else bought the last M meanwhile
        $this->checkoutSession();

        $this->post(route('checkout.place'), ['payment_method' => 'cod'])
            ->assertRedirect(route('cart'))
            ->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_cancelling_puts_stock_back_into_the_same_variant()
    {
        $this->add('XL', 2);
        $this->checkoutSession();
        $this->post(route('checkout.place'), ['payment_method' => 'cod']);
        $order = Order::first();

        $this->put(route('orders.cancel', [$order->encryptedId()]))->assertSessionHas('success');

        $this->assertSame(3, $this->sku('XL')->qty);
        $this->assertSame(6, $this->product->fresh()->stockQty());
    }

    public function test_product_page_has_variant_data_and_from_price()
    {
        $response = $this->get($this->product->url())->assertOk();

        $response->assertSee('id="variant-data"', false)
            ->assertSee('product-variants.js')
            ->assertSee('<span class="price__from">From</span>', false)
            ->assertDontSee('Only 6 left');
        $this->assertNull($this->product->minQty(), 'per-size stock is shown after choosing a size');
    }

    public function test_bag_page_shows_the_variant_price_and_stock()
    {
        $this->add('XL', 3);

        $this->withCookie('cart-id', '123456789')->get(route('cart'))->assertOk()
            ->assertSee('₱700.00')
            ->assertSee('max="3"', false);
    }
}
