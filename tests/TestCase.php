<?php

namespace Tests;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderBilling;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Minimal reference data the storefront layouts expect (categories, size attribute, settings).
     */
    protected function seedStoreBasics(): void
    {
        if (Category::count() === 0) {
            foreach (['hoddie', 'jacket', 'others'] as $name) {
                Category::factory()->create(['name' => $name, 'slug' => $name, 'image' => '/img/collection/default/' . $name . '.jpg']);
            }
        }
        if (Attribute::count() === 0) {
            Attribute::factory()->create(['name' => 'size', 'slug' => 'size']);
        }
        if (GeneralSetting::count() === 0) {
            GeneralSetting::factory()->create();
        }
    }

    /**
     * A published product with stock and, optionally, size variants (S, M, L, XL).
     */
    protected function createProduct(int $stock = 10, bool $withSizes = true, array $attributes = []): Product
    {
        $this->seedStoreBasics();

        $product = Product::factory()->create(array_merge([
            'category_id' => Category::first()->id,
            'status' => 1,
            'regular_price' => 500,
        ], $attributes));

        Stock::factory()->create(['product_id' => $product->id, 'qty' => $stock]);

        if ($withSizes) {
            $size = Attribute::where('name', 'size')->first();
            ProductAttribute::factory()->create(['product_id' => $product->id, 'attribute_id' => $size->id]);
            foreach (['S', 'M', 'L', 'XL'] as $name) {
                ProductVariant::factory()->create(['product_id' => $product->id, 'attribute_id' => $size->id, 'name' => $name]);
            }
        }

        return $product->fresh();
    }

    protected function signInCustomer(?User $user = null): User
    {
        // Reload so every column (with database defaults) is present, as on a real request.
        $user = ($user ?: User::factory()->create())->fresh();
        $this->actingAs($user, 'web');

        return $user;
    }

    protected function signInAdmin(?Admin $admin = null): Admin
    {
        $admin = $admin ?: Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * A paid PayPal order: 2 x overshirt at 500 + 150 shipping = 1150, with shipping and billing details.
     */
    protected function createPaidOrder(?User $user = null, array $attributes = [], int $stock = 10): Order
    {
        $user = $user ?: User::factory()->create();
        $product = $this->createProduct($stock, true, ['name' => 'overshirt', 'regular_price' => 500, 'sku' => 'OVR-001']);
        $order = Order::factory()->create(array_merge(['user_id' => $user->id, 'gross_total' => 1150, 'net_total' => 1000, 'shipping_charge' => 150], $attributes));
        OrderProduct::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 2, 'price' => 500, 'properties' => ['size' => 'M']]);
        OrderShipping::create(['order_id' => $order->id, 'firstname' => 'Ana', 'lastname' => 'Cruz', 'street' => '1 Main St', 'city' => 'Quezon City', 'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100']);
        OrderBilling::create(['order_id' => $order->id, 'firstname' => 'Ana', 'lastname' => 'Cruz', 'email' => 'ana@example.test', 'street' => '1 Main St', 'city' => 'Quezon City', 'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100']);
        OrderPayment::create(['order_id' => $order->id, 'provider' => 'paypal', 'reference_number' => 'CAP-123', 'amount' => '1150', 'status' => 'paid']);

        return $order->fresh();
    }

    /** An admin account with a unique username and email (the factory's are fixed). */
    protected function makeAdmin(array $attributes = []): Admin
    {
        $n = Admin::withTrashed()->count() + 1;

        return Admin::factory()->create(array_merge(['username' => "staff{$n}", 'email' => "staff{$n}@coda.test"], $attributes));
    }
}
