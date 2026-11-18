<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantsAdminTest extends TestCase
{
    use RefreshDatabase;

    private Attribute $size;
    private Attribute $color;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStoreBasics();
        $this->size = Attribute::where('name', 'size')->first();
        $this->color = Attribute::factory()->create(['name' => 'color', 'slug' => 'color']);
        $this->signInAdmin();
    }

    private function form(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Linen overshirt', 'category_id' => \App\Models\Category::first()->id, 'sku' => 'OVR-1', 'barcode' => '123',
            'regular_price' => 1000, 'status' => 1, 'qty' => 7, 'image' => json_encode(['path' => '/img/products/default.jpg']),
        ], $overrides);
    }

    private function withVariants(array $skus, array $attributes): array
    {
        return [
            'hasVariant' => 'on',
            'attributes' => array_map(fn ($a) => json_encode($a), $attributes),
            'skus' => $skus,
        ];
    }

    public function test_creating_a_product_with_size_variants_saves_price_stock_and_photo_per_variant()
    {
        $this->postJson(route('admin.products.store'), $this->form($this->withVariants([
            ['options' => json_encode(['size' => 'S']), 'price' => '', 'qty' => 5, 'image' => ''],
            ['options' => json_encode(['size' => 'M']), 'price' => '1000', 'qty' => 3, 'image' => '/img/p/m.jpg', 'sku' => 'OVR-1-M'],
            ['options' => json_encode(['size' => 'XL']), 'price' => '1200', 'qty' => 0, 'image' => ''],
        ], [['id' => $this->size->id, 'variants' => ['S', 'M', 'XL']]])))->assertOk();

        $product = Product::where('name', 'Linen overshirt')->first();
        $skus = $product->skus()->get()->keyBy('option_key');

        $this->assertSame(['size:s', 'size:m', 'size:xl'], $product->skus()->pluck('option_key')->all());
        $this->assertNull($skus['size:s']->price, 'empty price = product price');
        $this->assertNull($skus['size:m']->price, 'same as product price is stored as "same"');
        $this->assertSame(1200.0, (float) $skus['size:xl']->price);
        $this->assertSame('/img/p/m.jpg', $skus['size:m']->image);
        $this->assertSame('OVR-1-M', $skus['size:m']->sku);
        $this->assertSame(8, $product->fresh()->stockQty(), 'stock = sum of variants, the single quantity is ignored');
        $this->assertSame(1000.0, (float) $product->fresh()->price_from);
        $this->assertSame(1200.0, (float) $product->fresh()->price_to);
        $this->assertTrue($product->fresh()->hasVariablePrice());
    }

    public function test_two_options_make_combinations()
    {
        $this->postJson(route('admin.products.store'), $this->form($this->withVariants([
            ['options' => json_encode(['size' => 'M', 'color' => 'Black']), 'qty' => 2],
            ['options' => json_encode(['size' => 'M', 'color' => 'Blue']), 'qty' => 1],
        ], [['id' => $this->size->id, 'variants' => ['M']], ['id' => $this->color->id, 'variants' => ['Black', 'Blue']]])))->assertOk();

        $product = Product::where('name', 'Linen overshirt')->first();
        $this->assertSame('M / Black', $product->skuFor(['color' => 'black', 'size' => 'm'])->label());
        $this->assertSame(3, $product->fresh()->stockQty());
    }

    public function test_at_most_two_options()
    {
        $third = Attribute::factory()->create(['name' => 'fit', 'slug' => 'fit']);

        $this->postJson(route('admin.products.store'), $this->form($this->withVariants([
            ['options' => json_encode(['size' => 'M', 'color' => 'Black', 'fit' => 'Slim']), 'qty' => 1],
        ], [['id' => $this->size->id, 'variants' => ['M']], ['id' => $this->color->id, 'variants' => ['Black']], ['id' => $third->id, 'variants' => ['Slim']]])))
            ->assertStatus(422)->assertJsonValidationErrors('attributes');
    }

    public function test_every_variant_needs_a_quantity()
    {
        $this->postJson(route('admin.products.store'), $this->form($this->withVariants([
            ['options' => json_encode(['size' => 'M']), 'qty' => ''],
        ], [['id' => $this->size->id, 'variants' => ['M']]])))
            ->assertStatus(422)->assertJsonValidationErrors('skus.0.qty');
    }

    public function test_editing_keeps_variant_ids_removes_dropped_variants_and_their_bag_lines()
    {
        $product = $this->createProduct(0, true, ['name' => 'tee', 'regular_price' => 500]);
        app(\App\Services\Products\ProductSkus::class)->sync($product, [
            ['options' => ['size' => 'S'], 'qty' => 2], ['options' => ['size' => 'M'], 'qty' => 2],
        ]);
        $s = $product->skuFor(['size' => 'S']);
        $m = $product->skuFor(['size' => 'M']);
        $cart = Cart::factory()->create();
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'sku_id' => $m->id, 'qty' => 1, 'attributes' => ['size' => 'M']]);

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), $this->form(['name' => 'tee', 'regular_price' => 500, 'sku' => 'T', 'qty' => 0] + $this->withVariants([
            ['options' => json_encode(['size' => 'S']), 'qty' => 9],
        ], [['id' => $this->size->id, 'variants' => ['S']]])))->assertOk();

        $this->assertSame($s->id, $product->skuFor(['size' => 'S'])->id, 'same row kept');
        $this->assertSame(9, $s->fresh()->qty);
        $this->assertNull(ProductSku::find($m->id));
        $this->assertSame(0, CartItem::where('sku_id', $m->id)->count());
        $this->assertSame(9, $product->fresh()->stockQty());
    }

    public function test_turning_options_off_removes_variants_and_uses_the_single_quantity()
    {
        $product = $this->createProduct(0, true, ['name' => 'tee', 'regular_price' => 500]);
        app(\App\Services\Products\ProductSkus::class)->sync($product, [['options' => ['size' => 'S'], 'qty' => 2]]);

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), $this->form(['name' => 'tee', 'regular_price' => 500, 'sku' => 'T', 'qty' => 4]))->assertOk();

        $this->assertFalse($product->hasSkus());
        $this->assertNull($product->fresh()->price_from);
        $this->assertSame(4, $product->fresh()->stockQty());
    }

    public function test_variant_prices_follow_the_product_sale_percentage()
    {
        $product = $this->createProduct(0, true, ['regular_price' => 1000, 'sale_price' => 800, 'on_sale' => true]);
        app(\App\Services\Products\ProductSkus::class)->sync($product, [
            ['options' => ['size' => 'M'], 'qty' => 1],
            ['options' => ['size' => 'XL'], 'price' => 1500, 'qty' => 1],
        ]);
        $product = $product->fresh();

        $this->assertSame(800.0, $product->skuFor(['size' => 'M'])->effectivePrice());
        $this->assertSame(1200.0, $product->skuFor(['size' => 'XL'])->effectivePrice(), '20% off 1,500');
        $this->assertSame(800.0, $product->effectivePrice(), 'from price');
    }

    public function test_product_form_shows_the_variant_table_with_saved_values()
    {
        $product = $this->createProduct(0, true, ['regular_price' => 500]);
        app(\App\Services\Products\ProductSkus::class)->sync($product, [['options' => ['size' => 'M'], 'price' => 650, 'qty' => 4]]);

        $this->get(route('admin.products.edit', [$product->encryptedId()]))->assertOk()
            ->assertSee('id="variants-card"', false)
            ->assertSee('&quot;key&quot;:&quot;size:m&quot;', false)
            ->assertSee('&quot;name&quot;:&quot;size&quot;,&quot;values&quot;:[&quot;S&quot;,&quot;M&quot;,&quot;L&quot;,&quot;XL&quot;]', false)
            ->assertSee('admin-variants.js');
        $this->get(route('admin.products.create'))->assertOk()->assertSee('Add options like size or colour')->assertDontSee('has-variant');
    }

    public function test_migration_splits_existing_stock_evenly_and_flags_it_for_review()
    {
        $product = $this->createProduct(10, true); // S, M, L, XL
        \Illuminate\Support\Facades\DB::table('product_skus')->delete();
        $product->forceFill(['variant_stock_reviewed' => true])->saveQuietly();

        // Run the migration's data step again on this database.
        $migration = new \CreateProductSkusTable;
        $split = new \ReflectionMethod($migration, 'splitExistingStock');
        $split->setAccessible(true);
        $split->invoke($migration);

        $qtys = ProductSku::where('product_id', $product->id)->orderBy('position')->pluck('qty', 'option_key')->all();
        $this->assertSame(['size:s' => 3, 'size:m' => 3, 'size:l' => 2, 'size:xl' => 2], $qtys);
        $this->assertFalse((bool) $product->fresh()->variant_stock_reviewed);
    }

    public function test_saving_with_variants_works_without_the_single_quantity_field()
    {
        // The browser doesn't send the hidden, disabled Quantity box when variants are on.
        $form = $this->form($this->withVariants([
            ['options' => json_encode(['size' => 'S']), 'qty' => 4],
            ['options' => json_encode(['size' => 'M']), 'qty' => 7],
        ], [['id' => $this->size->id, 'variants' => ['S', 'M']]]));
        unset($form['qty']);

        $this->postJson(route('admin.products.store'), $form)->assertOk();
        $product = Product::where('name', 'Linen overshirt')->first();
        $this->assertSame(11, $product->stockQty());

        unset($form['hasVariant'], $form['attributes'], $form['skus']);
        $form['name'] = 'Linen overshirt';
        $this->putJson(route('admin.products.update', [$product->encryptedId()]), $form + $this->withVariants([
            ['options' => json_encode(['size' => 'S']), 'qty' => 1],
        ], [['id' => $this->size->id, 'variants' => ['S']]]))->assertOk();
        $this->assertSame(1, $product->fresh()->stockQty());
    }
}
