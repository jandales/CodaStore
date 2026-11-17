<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFiltersTest extends TestCase
{
    use RefreshDatabase;

    /** Names of the products on a shop page, in order. */
    private function names(string $url): array
    {
        return $this->get($url)->assertOk()->viewData('products')->pluck('name')->all();
    }

    public function test_draft_products_are_hidden_from_the_shop_and_their_page_404s()
    {
        $draft = $this->createProduct(5, true, ['name' => 'draft coat', 'status' => 0]);
        $this->createProduct(5, true, ['name' => 'live tee']);

        $this->assertSame(['live tee'], $this->names('/shop'));
        $this->get(route('shop.product', [$draft->encryptedId()]))->assertNotFound();
    }

    public function test_filter_by_size()
    {
        $this->createProduct(5, true, ['name' => 'sized tee']); // S, M, L, XL
        $this->createProduct(5, false, ['name' => 'one size cap']);

        $this->assertSame(['sized tee'], $this->names('/shop?size[]=M'));
    }

    public function test_filter_by_price_uses_the_price_customers_pay()
    {
        $this->createProduct(5, false, ['name' => 'cheap', 'regular_price' => 300]);
        $this->createProduct(5, false, ['name' => 'discounted', 'regular_price' => 2000, 'sale_price' => 900, 'on_sale' => true]);
        $this->createProduct(5, false, ['name' => 'expensive', 'regular_price' => 2500]);

        $names = $this->names('/shop?price_min=500&price_max=1000');

        $this->assertSame(['discounted'], $names);
    }

    public function test_on_sale_and_in_stock_filters()
    {
        $this->createProduct(5, false, ['name' => 'sale item', 'regular_price' => 1000, 'sale_price' => 700, 'on_sale' => true]);
        $this->createProduct(0, false, ['name' => 'sold out item']);
        $this->createProduct(5, false, ['name' => 'full price item']);

        $this->assertSame(['sale item'], $this->names('/shop?sale=1'));
        $this->assertEqualsCanonicalizing(['sale item', 'full price item'], $this->names('/shop?in_stock=1'));
    }

    public function test_sorting_by_price_and_name()
    {
        $this->createProduct(5, false, ['name' => 'bravo', 'regular_price' => 900]);
        $this->createProduct(5, false, ['name' => 'alpha', 'regular_price' => 1500, 'sale_price' => 500, 'on_sale' => true]);
        $this->createProduct(5, false, ['name' => 'charlie', 'regular_price' => 700]);

        $this->assertSame(['alpha', 'charlie', 'bravo'], $this->names('/shop?sort=price-asc'), 'sale price counts');
        $this->assertSame(['bravo', 'charlie', 'alpha'], $this->names('/shop?sort=price-desc'));
        $this->assertSame(['alpha', 'bravo', 'charlie'], $this->names('/shop?sort=name-asc'));
        $this->assertSame(['charlie', 'bravo', 'alpha'], $this->names('/shop?sort=name-desc'));
    }

    public function test_filters_combine_with_a_category_and_survive_pagination_links()
    {
        $product = $this->createProduct(5, true, ['name' => 'jacket one']);

        $response = $this->get('/shop/' . $product->category->slug . '?size[]=L&sort=price-asc');

        $response->assertOk()->assertSee('jacket one');
        $this->assertStringContainsString('sort=price-asc', $response->viewData('products')->url(2));
    }

    public function test_old_sort_links_redirect_to_the_new_shop()
    {
        $this->get('/shop/sort-by/low-to-high')->assertRedirect(route('shop', ['sort' => 'price-asc']));
        $this->get('/shop/sort-by/featured-product')->assertRedirect(route('shop', ['sort' => 'featured']));
        $this->get('/shop/sort-by/all')->assertRedirect(route('shop'));
    }

    public function test_filter_panel_shows_sizes_and_active_filter_chips()
    {
        $this->createProduct(5, true);

        $this->get('/shop?size[]=M&sale=1')
            ->assertOk()
            ->assertSee('Apply filters')
            ->assertSee('Size M')
            ->assertSee('On sale')
            ->assertSee('Clear all');
    }

    public function test_available_sizes_are_in_garment_order()
    {
        $product = $this->createProduct(5, false);
        foreach (['XL', 'S', 'M', 'L'] as $name) {
            ProductVariant::factory()->create(['product_id' => $product->id, 'attribute_id' => 1, 'name' => $name]);
        }

        $this->assertSame(['S', 'M', 'L', 'XL'], \App\Services\ShopServices::availableSizes());
    }
}
