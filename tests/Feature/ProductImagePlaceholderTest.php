<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImagePlaceholderTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_with_a_photo_shows_the_photo()
    {
        $product = $this->createProduct(10, false, ['imagePath' => '/img/products/default/1.webp']);

        $this->assertTrue($product->hasImage());
        $this->assertSame('/img/products/default/1.webp', $product->imageUrl());

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee('src="/img/products/default/1.webp"', false);
    }

    public function test_default_picture_and_missing_files_get_the_blurred_placeholder()
    {
        foreach ([Product::DEFAULT_IMAGE, '/img/products/does-not-exist.webp'] as $path) {
            $product = $this->createProduct(10, false, ['imagePath' => $path]);

            $this->assertFalse($product->hasImage(), $path);
            $this->assertStringStartsWith('data:image/svg+xml;base64,', $product->imageUrl());

            $svg = base64_decode(substr($product->imageUrl(), strlen('data:image/svg+xml;base64,')));
            $this->assertStringContainsString('feGaussianBlur', $svg);
        }
    }

    public function test_placeholder_colours_stay_the_same_for_a_product_and_differ_between_products()
    {
        $a = $this->createProduct(10, false, ['imagePath' => Product::DEFAULT_IMAGE]);
        $b = $this->createProduct(10, false, ['imagePath' => Product::DEFAULT_IMAGE]);

        $this->assertSame($a->imageUrl(), $a->fresh()->imageUrl());
        $this->assertNotSame($a->imageUrl(), $b->imageUrl());
    }

    public function test_shop_and_product_page_show_the_placeholder_instead_of_a_missing_image()
    {
        $product = $this->createProduct(10, false, ['imagePath' => '/img/products/does-not-exist.webp']);

        $this->get(route('shop'))->assertOk()
            ->assertSee($product->imageUrl(), false)
            ->assertDontSee('does-not-exist.webp', false);

        $this->get(route('shop.product', [$product->encryptedId()]))->assertOk()
            ->assertSee('id="fullImage" src="data:image/svg+xml;base64,', false);
    }

    public function test_thumbnails_with_missing_files_get_placeholders_and_real_ones_keep_their_photo()
    {
        $product = $this->createProduct(10, false, ['imagePath' => Product::DEFAULT_IMAGE]);
        $photo = fn ($path) => \App\Models\Photo::create(['product_id' => $product->id, 'path' => $path, 'location' => $path]);
        $missing1 = $photo('/img/products/gone-1.webp');
        $real = $photo('/img/products/default/3.webp');
        $missing2 = $photo('/img/products/gone-2.webp');
        $product = $product->fresh();

        // The first thumbnail matches the main image; the others get their own colours.
        $this->assertSame($product->imageUrl(), $product->photoUrl($missing1, 0));
        $this->assertSame('/img/products/default/3.webp', $product->photoUrl($real, 1));
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $product->photoUrl($missing2, 2));
        $this->assertNotSame($product->photoUrl($missing1, 0), $product->photoUrl($missing2, 2));

        $this->get(route('shop.product', [$product->encryptedId()]))->assertOk()
            ->assertSee('class="thumbnail-0" src="data:image/svg+xml;base64,', false)
            ->assertSee('class="thumbnail-1" src="/img/products/default/3.webp"', false)
            ->assertSee('class="thumbnail-2" src="data:image/svg+xml;base64,', false)
            ->assertDontSee('gone-1.webp', false)
            ->assertDontSee('gone-2.webp', false);
    }

    public function test_variant_without_its_own_photo_uses_the_product_image()
    {
        $product = $this->createProduct(10, false, ['imagePath' => Product::DEFAULT_IMAGE]);
        $sku = new \App\Models\ProductSku(['image' => '/img/products/missing-variant.webp']);

        $this->assertSame($product->imageUrl(), $product->imageUrlFor($sku));
        $this->assertSame($product->imageUrl(), $product->imageUrlFor(null));

        $sku->image = '/img/products/default/2.webp';
        $this->assertSame('/img/products/default/2.webp', $product->imageUrlFor($sku));
    }
}
