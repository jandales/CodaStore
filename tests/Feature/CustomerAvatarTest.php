<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CustomerAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_customers_get_a_neutral_placeholder_not_a_stock_photo()
    {
        $this->seedStoreBasics();

        $this->post(route('register.store'), [
            'name' => 'Ana Cruz', 'email' => 'ana@example.test', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1',
        ]);

        $user = User::where('email', 'ana@example.test')->first();
        $this->assertNull($user->imagePath);
        $this->assertFalse($user->hasAvatar());
        $this->assertSame('/img/avatar/placeholder.svg', $user->avatar());
        $this->assertFileExists(public_path('img/avatar/placeholder.svg'));

        $this->get(route('account'))
            ->assertSee('/img/avatar/placeholder.svg')
            ->assertDontSee('default-avatar.jpg');
    }

    public function test_existing_customers_with_the_stock_photo_are_moved_to_the_placeholder()
    {
        $stock = User::factory()->create(['imagePath' => '/img/avatar/default-avatar.jpg']);
        $own = User::factory()->create(['imagePath' => '/img/avatar/2022165166317560136.jpg']);

        (new \ClearStockCustomerAvatar)->up();

        $this->assertNull($stock->fresh()->imagePath);
        $this->assertSame('/img/avatar/2022165166317560136.jpg', $own->fresh()->imagePath, 'uploaded photos are kept');
    }

    public function test_uploading_replaces_the_old_photo_and_never_deletes_shared_images()
    {
        $user = $this->signInCustomer(User::factory()->create(['imagePath' => '/img/avatar/default-avatar.jpg']));

        $this->post(route('upload.avatar'), ['avatar' => UploadedFile::fake()->create('me.jpg', 8, 'image/jpeg')]);

        $first = $user->fresh()->imagePath;
        $this->assertStringStartsWith('/img/avatar/', $first);
        $this->assertFileExists(public_path($first));
        $this->assertFileExists(public_path('img/avatar/default-avatar.jpg'), 'the shared image is not deleted');

        sleep(1); // file names are time based
        $this->post(route('upload.avatar'), ['avatar' => UploadedFile::fake()->create('me2.jpg', 9, 'image/jpeg')]);
        $second = $user->fresh()->imagePath;

        $this->assertNotSame($first, $second);
        $this->assertFileDoesNotExist(public_path($first), 'the previous upload is cleaned up');
        $this->assertFileExists(public_path($second));

        File::delete(public_path($second));
    }

    public function test_photos_from_google_are_shown_as_is()
    {
        $user = User::factory()->create(['imagePath' => 'https://lh3.googleusercontent.com/a/photo']);

        $this->assertSame('https://lh3.googleusercontent.com/a/photo', $user->avatar());
    }
}
