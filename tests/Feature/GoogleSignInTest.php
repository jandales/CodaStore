<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    // A real-length Google account id: bigger than a BIGINT can hold.
    private const GOOGLE_ID = '109876543210987654321';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStoreBasics();
    }

    private function googleReturns(array $overrides = [], bool $verified = true): void
    {
        $data = array_merge(['id' => self::GOOGLE_ID, 'name' => 'Ana Cruz', 'email' => 'Ana@Example.test', 'avatar' => 'https://lh3.googleusercontent.com/a/ana'], $overrides);
        $user = (new GoogleUser)->setRaw(['email_verified' => $verified])->map($data);

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_new_customer_signs_up_with_a_21_digit_google_id()
    {
        $this->googleReturns();

        $this->get(route('handleProviderCallback'))->assertRedirect(route('home'));

        $user = User::where('email', 'ana@example.test')->first();
        $this->assertSame(self::GOOGLE_ID, (string) $user->provider_id, 'the full id is stored');
        $this->assertSame('google', $user->provider);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('https://lh3.googleusercontent.com/a/ana', $user->imagePath);
        $this->assertAuthenticatedAs($user);
    }

    public function test_signing_in_again_finds_the_same_account()
    {
        $this->googleReturns();
        $this->get(route('handleProviderCallback'));
        auth()->logout();

        $this->get(route('handleProviderCallback'));

        $this->assertSame(1, User::count());
    }

    public function test_existing_email_account_is_linked_when_google_has_verified_the_email()
    {
        $existing = User::factory()->create(['email' => 'ana@example.test', 'imagePath' => null]);
        $this->googleReturns();

        $this->get(route('handleProviderCallback'))->assertRedirect(route('home'));

        $this->assertSame(1, User::count(), 'no duplicate account');
        $existing->refresh();
        $this->assertSame(self::GOOGLE_ID, (string) $existing->provider_id);
        $this->assertSame('https://lh3.googleusercontent.com/a/ana', $existing->imagePath);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_existing_photo_is_kept_when_linking()
    {
        $existing = User::factory()->create(['email' => 'ana@example.test', 'imagePath' => '/img/avatar/mine.jpg']);
        $this->googleReturns();

        $this->get(route('handleProviderCallback'));

        $this->assertSame('/img/avatar/mine.jpg', $existing->fresh()->imagePath);
    }

    public function test_existing_account_is_not_linked_when_google_has_not_verified_the_email()
    {
        $existing = User::factory()->create(['email' => 'ana@example.test']);
        $this->googleReturns([], false);

        $this->get(route('handleProviderCallback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'An account with this email already exists. Sign in with your email and password.');

        $this->assertNull($existing->fresh()->provider_id);
        $this->assertGuest();
    }

    public function test_cancelling_on_google_returns_to_sign_in_with_a_message()
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andThrow(new \Laravel\Socialite\Two\InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('handleProviderCallback') . '?error=access_denied')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_customer_returns_to_the_page_they_were_going_to()
    {
        $this->googleReturns();
        $this->get(route('checkout.information'))->assertRedirect(route('login')); // remembers checkout

        $this->get(route('handleProviderCallback'))->assertRedirect(route('checkout.information'));
    }

    public function test_a_remembered_admin_page_is_never_used_after_customer_sign_in()
    {
        $this->googleReturns();
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login')); // signed out: admin sign-in, not the shop's

        $this->withSession(['url.intended' => url('/admin/dashboard')])
            ->get(route('handleProviderCallback'))
            ->assertRedirect(route('home'));
    }

    public function test_signed_out_admin_pages_go_to_the_admin_sign_in()
    {
        $this->get('/admin/orders')->assertRedirect(route('admin.login'));
        $this->get('/account')->assertRedirect(route('login'));
    }
}
