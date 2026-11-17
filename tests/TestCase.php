<?php

namespace Tests;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\GeneralSetting;
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
        $user = $user ?: User::factory()->create();
        $this->actingAs($user, 'web');

        return $user;
    }

    protected function signInAdmin(?Admin $admin = null): Admin
    {
        $admin = $admin ?: Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }
}
