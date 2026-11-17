<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStoreBasics();
    }

    /** @dataProvider publicPages */
    public function test_public_pages_load(string $url)
    {
        $this->createProduct();

        $this->get($url)->assertOk();
    }

    public function publicPages(): array
    {
        return [
            'home' => ['/'],
            'shop' => ['/shop'],
            'search' => ['/search?keyword=a'],
            'about' => ['/about'],
            'contact' => ['/contact'],
            'cart' => ['/cart'],
            'login' => ['/login'],
            'register' => ['/register'],
        ];
    }

    public function test_product_page_loads()
    {
        $product = $this->createProduct();

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_unknown_page_returns_branded_404()
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee("We couldn't find that page", false);
    }
}
