<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** The Media card on the product form: uploads, order, main image and removal. */
class ProductMediaTest extends TestCase
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

    /** A saved photo with a real file, so isUsableImage() accepts it. */
    private function photo(?Product $product, string $name): Photo
    {
        $path = $this->makeFile('/img/test/zz-media-' . $name . '-' . uniqid() . '.jpg');

        return Photo::create(['product_id' => optional($product)->id, 'path' => $path, 'location' => '/img/test/']);
    }

    /** A real JPEG upload (works without the GD extension, unlike UploadedFile::fake()->image()). */
    private function jpegUpload(string $name = 'shirt.jpg'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'media');
        copy(public_path('img/products/default.jpg'), $tmp);

        return new UploadedFile($tmp, $name, 'image/jpeg', null, true);
    }

    private function item(Photo $photo, int $deleted = 0): string
    {
        return json_encode(['id' => $photo->id, 'path' => $photo->path, 'deleted' => $deleted]);
    }

    /** What the product form sends, as the old compiled products.js does (image = "{}"). */
    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'linen shirt', 'category_id' => \App\Models\Category::first()->id, 'sku' => 'LS-1', 'barcode' => '1',
            'regular_price' => 500, 'status' => 1, 'qty' => 5, 'image' => '{}', 'media' => 1,
        ], $overrides);
    }

    public function test_uploading_photos_is_validated()
    {
        $this->signInAdmin();

        $this->postJson('/admin/image/uploads', ['images' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')]])
            ->assertStatus(422)
            ->assertJsonPath('errors', fn ($errors) => str_contains(json_encode($errors), 'JPG, PNG or WebP'));

        $this->postJson('/admin/image/uploads', ['images' => [UploadedFile::fake()->create('huge.jpg', 6000, 'image/jpeg')]])
            ->assertStatus(422)
            ->assertJsonPath('errors', fn ($errors) => str_contains(json_encode($errors), '5 MB or smaller'));

        $this->postJson('/admin/image/uploads', [])->assertStatus(422);
    }

    public function test_a_valid_photo_is_uploaded()
    {
        $this->signInAdmin();

        $response = $this->postJson('/admin/image/uploads', ['images' => [$this->jpegUpload()]])
            ->assertOk()
            ->assertJsonStructure(['images' => [['id', 'path', 'deleted']]]);

        $path = $response->json('images.0.path');
        $this->files[] = $path;
        $this->assertFileExists(public_path($path));
        $this->assertDatabaseHas('photos', ['id' => $response->json('images.0.id'), 'product_id' => null]);
    }

    public function test_creating_a_product_saves_the_photos_in_order_and_the_first_is_the_main_image()
    {
        $this->seedStoreBasics();
        $this->signInAdmin();
        $a = $this->photo(null, 'a');
        $b = $this->photo(null, 'b');
        $c = $this->photo(null, 'c');

        $this->postJson(route('admin.products.store'), $this->formData([
            'images' => [$this->item($b), $this->item($c), $this->item($a)],
        ]))->assertOk();

        $product = Product::latest('id')->first();
        $this->assertSame($b->path, $product->imagePath);
        $this->assertSame([$b->id, $c->id, $a->id], $product->photos->pluck('id')->all());
        $this->assertSame([0, 1, 2], $product->photos->pluck('sort_order')->all());
    }

    public function test_reordering_changes_the_main_image_and_removing_every_photo_gives_the_default()
    {
        $product = $this->createProduct(5, false, ['sku' => 'LS-1']);
        $a = $this->photo($product, 'a');
        $b = $this->photo($product, 'b');
        $product->forceFill(['imagePath' => $a->path])->save();
        $this->signInAdmin();
        $url = route('admin.products.update', [$product->encryptedId()]);

        $this->putJson($url, $this->formData(['images' => [$this->item($b), $this->item($a)]]))->assertOk();
        $this->assertSame($b->path, $product->fresh()->imagePath);
        $this->assertSame([$b->id, $a->id], $product->fresh()->photos->pluck('id')->all());
        $this->assertFileExists(public_path($a->path), 'no longer the main image, but still a photo');

        $this->putJson($url, $this->formData(['images' => [$this->item($b, 1), $this->item($a, 1)]]))->assertOk();
        $this->assertSame(Product::DEFAULT_IMAGE, $product->fresh()->imagePath);
        $this->assertCount(0, $product->fresh()->photos);
        $this->assertFileDoesNotExist(public_path($a->path));
        $this->assertFileDoesNotExist(public_path($b->path), 'the old main image file is deleted too');
    }

    public function test_the_main_image_from_before_the_media_card_becomes_a_photo()
    {
        $main = $this->makeFile('/img/test/zz-media-old-main-' . uniqid() . '.jpg');
        $product = $this->createProduct(5, false, ['sku' => 'LS-1', 'imagePath' => $main]);
        $gallery = $this->photo($product, 'gallery');
        $this->signInAdmin();

        // The edit page lists the old main image first, without an id.
        $this->get(route('admin.products.edit', [$product->encryptedId()]))->assertOk()
            ->assertSee(e(json_encode([['id' => null, 'path' => $main], ['id' => $gallery->id, 'path' => $gallery->path]])), false);

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), $this->formData([
            'images' => [json_encode(['id' => null, 'path' => $main, 'deleted' => 0]), $this->item($gallery)],
        ]))->assertOk();

        $photos = $product->fresh()->photos;
        $this->assertSame([$main, $gallery->path], $photos->pluck('path')->all());
        $this->assertSame($main, $product->fresh()->imagePath);
    }

    public function test_a_path_that_is_not_a_local_image_is_never_added()
    {
        $product = $this->createProduct(5, false, ['sku' => 'LS-1']);
        $this->signInAdmin();

        $this->putJson(route('admin.products.update', [$product->encryptedId()]), $this->formData([
            'images' => [
                json_encode(['id' => null, 'path' => '/img/test/does-not-exist.jpg', 'deleted' => 0]),
                json_encode(['id' => null, 'path' => 'https://example.test/x.jpg', 'deleted' => 0]),
                json_encode(['id' => null, 'path' => '../.env', 'deleted' => 0]),
            ],
        ]))->assertOk();

        $this->assertCount(0, $product->fresh()->photos);
        $this->assertSame(Product::DEFAULT_IMAGE, $product->fresh()->imagePath);
    }

    public function test_storefront_thumbnails_follow_the_saved_order()
    {
        $product = $this->createProduct(5, false);
        $a = $this->photo($product, 'a');
        $b = $this->photo($product, 'b');
        $a->update(['sort_order' => 1]);
        $b->update(['sort_order' => 0]);

        $this->get(route('shop.product', [$product->encryptedId()]))->assertOk()
            ->assertSeeInOrder(['class="thumbnail-0" src="' . $b->path . '"', 'class="thumbnail-1" src="' . $a->path . '"'], false);
    }

    public function test_product_forms_show_the_media_card_above_pricing()
    {
        $product = $this->createProduct(5, false);
        $this->signInAdmin();

        foreach ([route('admin.products.create'), route('admin.products.edit', [$product->encryptedId()])] as $url) {
            $this->get($url)->assertOk()
                ->assertSeeInOrder(['id="media-card"', 'Media', 'Pricing'], false)
                ->assertSee('js/admin-media.js', false)
                ->assertDontSee('Image Product')
                ->assertDontSee('Image gallery')
                ->assertDontSee('id="file-product-image"', false);
        }
    }
}
