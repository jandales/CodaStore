<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seedStoreBasics();
    }

    private function csv(array $rows): UploadedFile
    {
        $lines = array_map(function ($row) {
            $out = fopen('php://temp', 'r+');
            fputcsv($out, $row);
            rewind($out);
            return rtrim(stream_get_contents($out), "\n");
        }, $rows);

        return UploadedFile::fake()->createWithContent('products.csv', "\xEF\xBB\xBF" . implode("\n", $lines) . "\n");
    }

    private function preview(array $rows)
    {
        return $this->post(route('admin.products.import.preview'), ['file' => $this->csv($rows)]);
    }

    public function test_dry_run_reports_every_row_and_changes_nothing()
    {
        $this->createProduct(10, false, ['name' => 'old tee', 'sku' => 'TEE-1', 'regular_price' => 500]);
        $this->signInAdmin();

        $response = $this->preview([
            ['Name', 'SKU', 'Category', 'Status', 'Regular price', 'Sale price', 'On sale', 'Stock', 'Sizes', 'Price now'],
            ['linen shirt', 'SH-1', 'jacket', 'Published', '1,899', '1499', 'Yes', '25', 'S|M|L', '1499'],
            ['', 'TEE-1', '', '', '450', '', '', '3', '', ''],
            ['bad one', 'BAD-1', 'shoes', '', 'abc', '', 'maybe', '-1', '', ''],
        ])->assertOk();

        $report = $response->viewData('report');
        $this->assertSame(['create' => 1, 'update' => 1, 'error' => 1], $report['counts']);
        $this->assertSame('Price ₱1,899.00, on sale at ₱1,499.00, stock 25, published, sizes S/M/L', implode(', ', $report['rows'][0]['changes']));
        $this->assertSame(['price ₱500.00 → ₱450.00', 'stock 10 → 3'], $report['rows'][1]['changes']);
        $this->assertCount(4, $report['rows'][2]['errors']);
        $response->assertSee('Import 2 products')->assertSee('Category "shoes" doesn\'t exist');

        $this->assertSame(1, Product::count(), 'nothing written yet');
        $this->assertSame(500, (int) Product::first()->regular_price);
    }

    public function test_confirm_imports_valid_rows_and_logs_one_summary()
    {
        $existing = $this->createProduct(10, false, ['name' => 'old tee', 'sku' => 'TEE-1', 'regular_price' => 500]);
        $admin = $this->signInAdmin();

        $token = $this->preview([
            ['Name', 'SKU', 'Category', 'Status', 'Regular price', 'Sale price', 'On sale', 'Stock', 'Sizes', 'Description'],
            ['linen shirt', 'SH-1', 'Jacket', 'Published', '1899', '1499', 'Yes', '25', 'S, M, l', 'Washed linen'],
            ['', 'TEE-1', '', 'Draft', '450', '', '', '3', '', ''],
            ['bad one', '', '', '', '', '', '', '', '', ''],
        ])->viewData('token');

        $this->post(route('admin.products.import.run'), ['token' => $token])
            ->assertRedirect(route('admin.products.import'))
            ->assertSessionHas('success', 'Import finished: 1 created, 1 updated, 1 rows with errors skipped.');

        $shirt = Product::where('sku', 'SH-1')->first();
        $this->assertSame('linen shirt', $shirt->name);
        $this->assertSame(1, (int) $shirt->status);
        $this->assertTrue($shirt->isOnSale());
        $this->assertSame(1499.0, (float) $shirt->effectivePrice());
        $this->assertSame(25, $shirt->stockQty());
        $this->assertSame('Washed linen', $shirt->short_description);
        $this->assertSame($admin->id, (int) $shirt->admin_id);
        $this->assertSame(['S', 'M', 'L'], ProductVariant::where('product_id', $shirt->id)->orderBy('id')->pluck('name')->all());

        $existing->refresh();
        $this->assertSame(450, (int) $existing->regular_price);
        $this->assertSame(0, (int) $existing->status);
        $this->assertSame(3, $existing->stockQty());
        $this->assertSame('old tee', $existing->name, 'empty cells leave values unchanged');

        $this->assertSame(['product.import'], ActivityLog::pluck('action')->all(), 'one summary, not one entry per change');
        $this->assertSame([], Storage::files('imports'), 'upload removed after import');
    }

    public function test_an_exported_file_imports_back_without_errors()
    {
        $this->createProduct(4, false, ['name' => 'sale coat', 'regular_price' => 2000, 'sale_price' => 1500, 'on_sale' => true, 'sku' => 'COAT-1']);
        $this->signInAdmin();
        $export = ltrim($this->get(route('admin.exports.products'))->getContent(), "\xEF\xBB\xBF");

        $file = UploadedFile::fake()->createWithContent('export.csv', $export);
        $report = $this->post(route('admin.products.import.preview'), ['file' => $file])->viewData('report');

        $this->assertSame(['create' => 0, 'update' => 1, 'error' => 0], $report['counts']);
        $this->assertSame(['No changes'], $report['rows'][0]['changes']);
        $this->assertSame([], $report['ignored']);
    }

    public function test_sale_rules_duplicates_and_bad_files()
    {
        $this->signInAdmin();

        $report = $this->preview([
            ['Name', 'SKU', 'Category', 'Regular price', 'Sale price', 'On sale'],
            ['a', 'A-1', 'jacket', '1000', '1200', 'Yes'],
            ['b', 'B-1', 'jacket', '1000', '', 'Yes'],
            ['c', 'C-1', 'jacket', '1000', '', ''],
            ['c again', 'c-1', 'jacket', '1000', '', ''],
        ])->viewData('report');

        $this->assertContains('The sale price must be lower than the regular price.', $report['rows'][0]['errors']);
        $this->assertContains('On sale is Yes but there is no sale price.', $report['rows'][1]['errors']);
        $this->assertSame([], $report['rows'][2]['errors']);
        $this->assertSame(['SKU c-1 is already used on row 4 of this file.'], $report['rows'][3]['errors']);

        $this->preview([['Colour', 'Size'], ['red', 'M']])->assertSessionHasErrors(['file' => 'The file needs a "Name" or "SKU" column. Download the template to see the expected columns.']);
        $this->preview([['Name', 'SKU']])->assertSessionHasErrors('file');
        $this->post(route('admin.products.import.preview'), ['file' => UploadedFile::fake()->create('photo.png', 10, 'image/png')])->assertSessionHasErrors('file');
    }

    public function test_expired_or_forged_tokens_are_rejected()
    {
        $this->signInAdmin();

        $this->post(route('admin.products.import.run'), ['token' => '../../.env'])->assertRedirect(route('admin.products.import'))->assertSessionHasErrors('file');
        $this->post(route('admin.products.import.run'), ['token' => str_repeat('a', 40)])->assertSessionHasErrors('file');
    }

    public function test_template_download_and_permission()
    {
        $this->signInAdmin();
        $this->get(route('admin.products.import'))->assertOk()->assertSee('Check file');
        $this->get(route('admin.products.import.template'))->assertOk()->assertSee('Name,SKU,Category', false);

        $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]));
        $this->get(route('admin.products.import'))->assertForbidden();
    }
}
