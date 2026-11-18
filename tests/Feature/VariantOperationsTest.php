<?php

namespace Tests\Feature;

use App\Mail\Admin\LowStockDigestMail;
use App\Models\ActivityLog;
use App\Models\GeneralSetting;
use App\Services\Products\ProductSkus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariantOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function tee(array $stock = ['S' => 20, 'M' => 2, 'L' => 0], array $prices = [], array $attrs = [])
    {
        $product = $this->createProduct(0, true, array_merge(['name' => 'tee', 'regular_price' => 500, 'sku' => 'TEE-1'], $attrs));
        app(ProductSkus::class)->sync($product, collect($stock)->map(fn ($qty, $size) => [
            'options' => ['size' => $size], 'qty' => $qty, 'price' => $prices[$size] ?? null,
        ])->values()->all());

        return $product->fresh();
    }

    // ---------------------------------------------------------------- low stock

    public function test_a_low_size_shows_as_low_stock_even_when_the_total_is_high()
    {
        $this->tee();
        $this->signInAdmin();

        $items = $this->get(route('admin.dashboard'))->assertOk()->viewData('low_stock');

        $this->assertSame(['tee (L)', 'tee (M)'], $items->pluck('name')->all(), 'sold out first, then 2 left');
        $this->get(route('admin.dashboard'))->assertSee('tee (M)')->assertSee('2 left')->assertSee('Sold out');
    }

    public function test_daily_digest_lists_low_sizes()
    {
        Mail::fake();
        $this->tee();
        GeneralSetting::first()->update(['site_email' => 'store@coda.test']);

        $this->artisan('store:low-stock-digest')->assertExitCode(0);

        Mail::assertSent(LowStockDigestMail::class, fn ($mail) => $mail->products->pluck('name')->all() === ['tee (L)', 'tee (M)']);
    }

    // ---------------------------------------------------------------- inventory

    public function test_inventory_adjusts_each_variant_and_logs_it()
    {
        $product = $this->tee();
        $m = $product->skuFor(['size' => 'M']);
        $this->signInAdmin();

        $this->get(route('admin.inventory'))->assertOk()
            ->assertSee(route('admin.inventory.update.variant', [$m->id]), false)
            ->assertSee('Adjust each variant below');

        $this->putJson(route('admin.inventory.update.variant', [$m->id]), ['qty' => 3, 'action' => 1])
            ->assertOk()->assertJson(['stock' => ['qty' => 5]]);
        $this->putJson(route('admin.inventory.update.variant', [$m->id]), ['qty' => 9, 'action' => 0])
            ->assertJson(['stock' => ['qty' => 0]]);

        $this->assertSame(20, $product->fresh()->stockQty(), 'total follows the variants');
        $this->assertContains('Stock for tee (M): 2 → 5 (+3)', ActivityLog::where('action', 'stock.changed')->pluck('description')->all());
    }

    public function test_stock_split_on_migration_can_be_checked_off()
    {
        $product = $this->tee();
        $product->forceFill(['variant_stock_reviewed' => false])->saveQuietly();
        $this->signInAdmin();

        $this->get(route('admin.dashboard'))->assertSee('had their stock split evenly');
        $this->get(route('admin.inventory.filter', ['needs-review']))->assertOk()->assertSee('Mark as checked');

        $this->put(route('admin.inventory.reviewed', [$product->encryptedId()]))->assertSessionHas('success');

        $this->assertTrue((bool) $product->fresh()->variant_stock_reviewed);
        $this->get(route('admin.dashboard'))->assertDontSee('had their stock split evenly');
    }

    // ---------------------------------------------------------------- shop

    public function test_size_filter_only_shows_products_with_that_size_in_stock()
    {
        $this->tee(['S' => 5, 'M' => 0]);
        $this->createProduct(5, true, ['name' => 'plain sized hoodie']); // sizes without variants yet

        $names = fn ($url) => $this->get($url)->viewData('products')->pluck('name')->all();

        $this->assertSame(['plain sized hoodie'], $names('/shop?size[]=M'), 'tee has no M left');
        $this->assertEqualsCanonicalizing(['tee', 'plain sized hoodie'], $names('/shop?size[]=S'));
    }

    public function test_price_sort_and_filter_use_the_lowest_variant_price()
    {
        $this->tee(['S' => 5, 'XL' => 5], ['S' => 300, 'XL' => 900]); // from 300
        $this->createProduct(5, false, ['name' => 'cap', 'regular_price' => 400]);

        $this->assertSame(['tee', 'cap'], $this->get('/shop?sort=price-asc')->viewData('products')->pluck('name')->all());
        $this->assertSame(['tee'], $this->get('/shop?price_max=350')->viewData('products')->pluck('name')->all());
    }

    // ---------------------------------------------------------------- CSV

    public function test_export_lists_variant_stock_and_prices()
    {
        $this->tee(['S' => 5, 'XL' => 2], ['XL' => 700]);
        $this->signInAdmin();

        $csv = $this->get(route('admin.exports.products'))->assertOk()->getContent();

        $this->assertStringContainsString('"Variant stock","Variant prices"', str_replace(',Variant stock,Variant prices', ',"Variant stock","Variant prices"', $csv));
        $this->assertStringContainsString('S:5|XL:2,XL:700.00', $csv);
    }

    public function test_import_updates_variant_stock_and_prices()
    {
        Storage::fake('local');
        $product = $this->tee(['S' => 5, 'M' => 2]);
        $this->signInAdmin();

        $file = UploadedFile::fake()->createWithContent('p.csv', "SKU,Variant stock,Variant prices\nTEE-1,S:9|M:0,M:650\n");
        $report = $this->post(route('admin.products.import.preview'), ['file' => $file])->viewData('report');
        $this->assertSame(['create' => 0, 'update' => 1, 'error' => 0], $report['counts']);
        $this->assertContains('variant stock S 5 → 9, M 2 → 0', $report['rows'][0]['changes']);

        $this->post(route('admin.products.import.run'), ['token' => $this->post(route('admin.products.import.preview'), ['file' => UploadedFile::fake()->createWithContent('p.csv', "SKU,Variant stock,Variant prices\nTEE-1,S:9|M:0,M:650\n")])->viewData('token')]);

        $product = $product->fresh();
        $this->assertSame(9, $product->skuFor(['size' => 'S'])->qty);
        $this->assertSame(650.0, (float) $product->skuFor(['size' => 'M'])->price);
        $this->assertSame(9, $product->stockQty());
    }

    public function test_import_rejects_unknown_variants_and_plain_stock_for_variant_products()
    {
        Storage::fake('local');
        $this->tee(['S' => 5, 'M' => 2]);
        $this->signInAdmin();

        $report = $this->post(route('admin.products.import.preview'), ['file' => UploadedFile::fake()->createWithContent('p.csv', "SKU,Variant stock\nTEE-1,XXL:4\n")])->viewData('report');
        $this->assertStringContainsString("\"XXL\" isn't one of this product's variants", implode(' ', $report['rows'][0]['errors']));

        $report = $this->post(route('admin.products.import.preview'), ['file' => UploadedFile::fake()->createWithContent('p.csv', "SKU,Stock\nTEE-1,40\n")])->viewData('report');
        $this->assertStringContainsString('set stock per variant', implode(' ', $report['rows'][0]['errors']));
    }
}
