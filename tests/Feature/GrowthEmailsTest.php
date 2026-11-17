<?php

namespace Tests\Feature;

use App\Mail\CartReminderMail;
use App\Mail\NewsletterWelcomeMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GrowthEmailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seedStoreBasics();
    }

    // ---------------------------------------------------------------- E3 email verification

    public function test_registering_sends_a_confirm_email_link()
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Ana Cruz', 'email' => 'ana@example.test', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1',
        ])->assertRedirect(route('account'));

        $user = User::where('email', 'ana@example.test')->first();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_customers_are_sent_to_confirm_before_checkout()
    {
        $user = User::factory()->unverified()->create();
        $this->signInCustomer($user);

        $this->get(route('checkout.information'))->assertRedirect(route('verification.notice'));
        $this->post(route('checkout.paypal.start'))->assertRedirect(route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk()->assertSee('Confirm your email')->assertSee($user->email);
        $this->get(route('account'))->assertSee('Please confirm your email address');
    }

    public function test_the_link_confirms_the_email()
    {
        $user = User::factory()->unverified()->create();
        $this->signInCustomer($user);
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->get($url)->assertRedirect(route('account'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_resend_link()
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->signInCustomer($user);

        $this->post(route('verification.send'))->assertSessionHas('success');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_existing_customers_count_as_verified_after_the_migration()
    {
        $user = User::factory()->unverified()->create();
        $migration = new \GrowthTables;
        $migration->down();
        $migration->up();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    // ---------------------------------------------------------------- E4 newsletter

    public function test_newsletter_signup_from_the_footer()
    {
        $this->get('/')->assertSee('Newsletter')->assertSee('name="website"', false);

        $this->from('/shop')->post(route('newsletter.subscribe'), ['email' => '  Ana@Example.test '])
            ->assertRedirect(url('/shop') . '#newsletter')
            ->assertSessionHas('newsletter_success');

        $subscriber = NewsletterSubscriber::first();
        $this->assertSame('ana@example.test', $subscriber->email);
        Mail::assertQueued(NewsletterWelcomeMail::class, fn ($mail) => $mail->hasTo('ana@example.test'));

        // Signing up again gives the same message and no second email.
        $this->from('/shop')->post(route('newsletter.subscribe'), ['email' => 'ana@example.test'])->assertSessionHas('newsletter_success');
        $this->assertSame(1, NewsletterSubscriber::count());
        Mail::assertQueued(NewsletterWelcomeMail::class, 1);
    }

    public function test_newsletter_validation_and_honeypot()
    {
        $this->from('/')->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])->assertSessionHasErrors('email', null, 'newsletter');
        $this->from('/')->post(route('newsletter.subscribe'), ['email' => 'bot@example.test', 'website' => 'http://spam'])->assertSessionHas('newsletter_success');
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_unsubscribe_needs_a_click_and_resubscribing_works()
    {
        $subscriber = NewsletterSubscriber::create(['email' => 'ana@example.test']);

        $this->get($subscriber->unsubscribeUrl())->assertOk()->assertSee('Unsubscribe?');
        $this->assertNull($subscriber->fresh()->unsubscribed_at, 'opening the link alone does nothing');

        $this->post(route('newsletter.unsubscribe.confirm', [$subscriber->token]))->assertOk()->assertSee("You're unsubscribed", false);
        $this->assertNotNull($subscriber->fresh()->unsubscribed_at);

        $this->from('/')->post(route('newsletter.subscribe'), ['email' => 'ana@example.test']);
        $this->assertNull($subscriber->fresh()->unsubscribed_at);
    }

    public function test_admin_exports_active_subscribers()
    {
        NewsletterSubscriber::create(['email' => 'on@example.test']);
        NewsletterSubscriber::create(['email' => 'off@example.test', 'unsubscribed_at' => now()]);
        $this->signInAdmin();

        $this->get(route('admin.customers'))->assertSee('Newsletter list (1)');
        $this->get(route('admin.exports.subscribers'))->assertOk()->assertSee('on@example.test')->assertDontSee('off@example.test');
    }

    // ---------------------------------------------------------------- E4 bag reminders

    private function abandonedCart(?User $user = null, int $hoursAgo = 30): Cart
    {
        $user = $user ?: User::factory()->create();
        $product = $this->createProduct(5, false, ['name' => 'linen shirt']);
        $cart = Cart::create(['cart_id' => random_int(1000, 999999), 'expired_at' => now()->addDays(5), 'user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'qty' => 1, 'attributes' => ['size' => 'M']]);
        Cart::whereKey($cart->id)->update(['updated_at' => now()->subHours($hoursAgo)]);

        return $cart->fresh();
    }

    public function test_adding_to_bag_while_signed_in_links_the_bag_to_the_customer()
    {
        $user = $this->signInCustomer();
        $product = $this->createProduct(5, false);

        $this->withCredentials()->withCookie('cart-id', '777')->postJson(route('cart.store', [$product->encryptedId()]), ['qty' => 1])->assertOk();

        $this->assertSame($user->id, (int) Cart::where('cart_id', 777)->value('user_id'));
    }

    public function test_reminder_is_sent_once_for_a_bag_left_a_day()
    {
        $cart = $this->abandonedCart();

        $this->artisan('store:cart-reminders')->assertExitCode(0);
        $this->artisan('store:cart-reminders')->assertExitCode(0);

        Mail::assertSent(CartReminderMail::class, 1);
        Mail::assertSent(CartReminderMail::class, fn ($mail) => $mail->hasTo($cart->user->email));
        $this->assertNotNull($cart->fresh()->reminded_at);
    }

    public function test_no_reminder_for_fresh_bags_opted_out_customers_or_after_an_order()
    {
        $this->abandonedCart(null, 2); // too recent
        $this->abandonedCart(User::factory()->create(['cart_reminders' => false]));
        $buyer = User::factory()->create();
        $this->abandonedCart($buyer);
        Order::factory()->create(['user_id' => $buyer->id]);

        $this->artisan('store:cart-reminders');

        Mail::assertNothingSent();
    }

    public function test_reminder_links_restore_the_bag_and_turn_reminders_off()
    {
        $cart = $this->abandonedCart();
        $mail = new CartReminderMail($cart->load('user', 'items.product'));
        $this->app->forgetInstance('mail.manager');
        $this->app->forgetInstance('mailer');
        Mail::clearResolvedInstance('mail.manager');
        $html = $mail->render();
        preg_match_all('/href="([^"]+)"/', $html, $links);
        $restore = collect($links[1])->first(fn ($l) => str_contains($l, '/cart/restore/'));
        $off = collect($links[1])->first(fn ($l) => str_contains($l, '/cart-reminders/off/'));
        $this->assertNotNull($restore);
        $this->assertStringContainsString('Linen Shirt', $html);
        $this->assertStringContainsString('(M)', $html);

        // Restoring needs the right customer to be signed in.
        $this->signInCustomer();
        $this->get(html_entity_decode($restore))->assertForbidden();
        $this->signInCustomer($cart->user);
        $this->get(html_entity_decode($restore))->assertRedirect(route('cart'))->assertCookie('cart-id');

        $this->get(html_entity_decode($off))->assertOk()->assertSee('Bag reminders are off');
        $this->assertFalse((bool) $cart->user->fresh()->cart_reminders);

        // Tampered links are rejected.
        $this->get(html_entity_decode($off) . 'x')->assertForbidden();
    }

    public function test_reminders_are_scheduled_hourly()
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $this->assertTrue($events->contains(fn ($e) => str_contains($e->command, 'store:cart-reminders') && $e->expression === '0 * * * *'));
    }
}
