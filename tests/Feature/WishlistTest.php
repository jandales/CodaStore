<?php

namespace Tests\Feature;

use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_heart_links_to_sign_in_and_posting_requires_login()
    {
        $product = $this->createProduct();

        $this->get('/shop')->assertOk()->assertSee('Sign in to save', false);
        $this->post(route('wishlist.toggle', [$product->encryptedId()]))->assertRedirect(route('login'));
        $this->assertSame(0, Wishlist::count());
    }

    public function test_heart_saves_and_unsaves_a_product()
    {
        $user = $this->signInCustomer();
        $product = $this->createProduct();

        $this->post(route('wishlist.toggle', [$product->encryptedId()]))->assertSessionHas('wishlist_message', 'Saved to your wishlist');
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->post(route('wishlist.toggle', [$product->encryptedId()]))->assertSessionHas('wishlist_message', 'Removed from your wishlist');
        $this->assertSame(0, Wishlist::count());
    }

    public function test_product_page_shows_saved_state()
    {
        $user = $this->signInCustomer();
        $product = $this->createProduct();
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->get(route('shop.product', [$product->encryptedId()]))
            ->assertOk()
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('Saved');
    }

    public function test_wishlist_page_lists_only_my_items_and_can_remove()
    {
        $user = $this->signInCustomer();
        $mine = $this->createProduct(5, true, ['name' => 'my linen shirt']);
        $theirs = $this->createProduct(5, true, ['name' => 'their wool coat']);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $mine->id]);
        Wishlist::create(['user_id' => User::factory()->create()->id, 'product_id' => $theirs->id]);

        $this->get(route('account.wishlist'))->assertOk()->assertSee('my linen shirt')->assertDontSee('their wool coat');

        $this->delete(route('wishlist.remove', [$mine->encryptedId()]))->assertSessionHas('wishlist_message');
        $this->assertSame(1, Wishlist::count(), 'only my item was removed');
    }

    public function test_empty_wishlist_message()
    {
        $this->seedStoreBasics();
        $this->signInCustomer();

        $this->get(route('account.wishlist'))->assertOk()->assertSee('Your wishlist is empty');
    }

    public function test_toggle_returns_json_for_ajax()
    {
        $this->signInCustomer();
        $product = $this->createProduct();

        $this->postJson(route('wishlist.toggle', [$product->encryptedId()]))->assertOk()->assertJson(['saved' => true]);
    }

    public function test_shop_cards_show_the_heart_icon_only()
    {
        $product = $this->createProduct(5, true, ['name' => 'linen shirt']);

        // Guest and signed-in, the card heart has no visible text (only an accessible name).
        $this->get('/shop')->assertOk()->assertDontSee('<span>Save to wishlist</span>', false);

        $user = $this->signInCustomer();
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);
        $this->get('/shop')->assertOk()
            ->assertDontSee('<span>Saved</span>', false)
            ->assertDontSee('<span>Save to wishlist</span>', false)
            ->assertSee('Remove linen shirt from wishlist');

        // The product page keeps its text label.
        $this->get($product->url())->assertSee('<span>Saved</span>', false);
    }
}
