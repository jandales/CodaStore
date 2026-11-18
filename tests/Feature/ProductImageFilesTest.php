<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Services\Products\ProductImageFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImageFilesTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    private function makeFile(string $path): string
    {
        File::ensureDirectoryExists(dirname(public_path($path)));
        File::put(public_path($path), 'image');
        $this->files[] = $path;

        return $path;
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $path) {
            File::delete(public_path($path));
        }
        parent::tearDown();
    }

    public function test_removing_a_gallery_photo_keeps_a_file_other_photos_still_use()
    {
        $path = $this->makeFile('/img/test/zz-shared-' . uniqid() . '.jpg');
        $product = $this->createProduct(5, false);
        $first = Photo::create(['product_id' => $product->id, 'path' => $path, 'location' => '/img/test/']);
        $second = Photo::create(['product_id' => $product->id, 'path' => $path, 'location' => '/img/test/']);
        $this->signInAdmin();

        $this->delete('/admin/image/' . $first->id . '/delete');
        $this->assertFileExists(public_path($path), 'still used by the other photo');

        $this->delete('/admin/image/' . $second->id . '/delete');
        $this->assertFileDoesNotExist(public_path($path), 'nothing uses it any more');
    }

    public function test_a_file_used_as_a_product_main_image_or_variant_photo_is_kept()
    {
        $main = $this->makeFile('/img/test/zz-main-' . uniqid() . '.jpg');
        $variant = $this->makeFile('/img/test/zz-variant-' . uniqid() . '.jpg');
        $this->createProduct(5, false, ['imagePath' => $main]);
        $product = $this->createProduct(0, true);
        app(\App\Services\Products\ProductSkus::class)->sync($product, [['options' => ['size' => 'M'], 'qty' => 1, 'image' => $variant]]);

        $this->assertFalse(ProductImageFiles::deleteIfUnused($main));
        $this->assertFalse(ProductImageFiles::deleteIfUnused($variant));
        $this->assertFileExists(public_path($main));
        $this->assertFileExists(public_path($variant));
    }

    public function test_sample_images_that_ship_with_the_project_are_never_deleted()
    {
        $this->assertFileExists(public_path('img/products/default/7.webp'));

        $this->assertFalse(ProductImageFiles::deleteIfUnused('/img/products/default/7.webp'));
        $this->assertFalse(ProductImageFiles::deleteIfUnused('/img/products/default.jpg'));
        $this->assertFalse(ProductImageFiles::deleteIfUnused('/img/../.env'));

        $this->assertFileExists(public_path('img/products/default/7.webp'));
    }

    public function test_removing_a_photo_when_saving_the_product_keeps_shared_files()
    {
        $path = $this->makeFile('/img/test/zz-gallery-' . uniqid() . '.jpg');
        $product = $this->createProduct(5, false, ['regular_price' => 500, 'sku' => 'P1']);
        $removed = Photo::create(['product_id' => $product->id, 'path' => $path, 'location' => '/img/test/']);
        $other = $this->createProduct(5, false);
        Photo::create(['product_id' => $other->id, 'path' => $path, 'location' => '/img/test/']);
        $this->signInAdmin();

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), [
            'name' => $product->name, 'category_id' => $product->category_id, 'sku' => 'P1', 'barcode' => '1',
            'regular_price' => 500, 'status' => 1, 'qty' => 5,
            'image' => json_encode(['path' => $product->imagePath]),
            'images' => [json_encode(['id' => $removed->id, 'path' => $path, 'deleted' => 1])],
        ])->assertOk();

        $this->assertNull(Photo::find($removed->id));
        $this->assertFileExists(public_path($path), 'the other product still shows it');
    }

    public function test_removing_a_photo_returns_whether_it_was_deleted()
    {
        $product = $this->createProduct(5, false);
        $photo = Photo::create(['product_id' => $product->id, 'path' => '/img/test/zz-gone.jpg', 'location' => '/img/test/']);
        $this->signInAdmin();

        $this->deleteJson('/admin/image/' . $photo->id . '/delete')->assertOk()->assertJson(['deleted' => true]);
        $this->assertNull(Photo::find($photo->id));
    }

    public function test_saving_a_product_with_a_photo_that_is_already_gone_still_works()
    {
        $product = $this->createProduct(5, false, ['regular_price' => 500, 'sku' => 'P1']);
        $kept = Photo::create(['product_id' => $product->id, 'path' => '/img/test/zz-kept.jpg', 'location' => '/img/test/']);
        $removed = Photo::create(['product_id' => $product->id, 'path' => '/img/test/zz-removed.jpg', 'location' => '/img/test/']);
        $upload = Photo::create(['product_id' => null, 'path' => '/img/test/zz-new.jpg', 'location' => '/img/test/']);
        $other = $this->createProduct(5, false);
        $notOurs = Photo::create(['product_id' => $other->id, 'path' => '/img/test/zz-other.jpg', 'location' => '/img/test/']);
        $this->signInAdmin();

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), [
            'name' => 'renamed shirt', 'category_id' => $product->category_id, 'sku' => 'P1', 'barcode' => '1',
            'regular_price' => 500, 'status' => 1, 'qty' => 5,
            'image' => json_encode(['path' => $product->imagePath]),
            'images' => [
                json_encode(['id' => 999999, 'path' => '/img/test/zz-missing.jpg', 'deleted' => 1]),  // removed earlier
                json_encode(['id' => 999998, 'path' => '/img/test/zz-missing2.jpg', 'deleted' => 0]),
                json_encode(['id' => $removed->id, 'path' => $removed->path, 'deleted' => 1]),
                json_encode(['id' => $kept->id, 'path' => $kept->path, 'deleted' => 0]),
                json_encode(['id' => $upload->id, 'path' => $upload->path, 'deleted' => 0]),
                json_encode(['id' => $notOurs->id, 'path' => $notOurs->path, 'deleted' => 1]),
            ],
        ])->assertOk();

        $this->assertSame('renamed shirt', $product->fresh()->name);
        $this->assertNull(Photo::find($removed->id));
        $this->assertNull(Photo::withoutGlobalScopes()->find($removed->id), 'a removed photo is not saved again');
        $this->assertSame($product->id, (int) $kept->fresh()->product_id);
        $this->assertSame($product->id, (int) $upload->fresh()->product_id, 'new uploads join the product');
        $this->assertNotNull($notOurs->fresh(), 'another product\'s photo is left alone');
        $this->assertSame($other->id, (int) $notOurs->fresh()->product_id);
    }
}
