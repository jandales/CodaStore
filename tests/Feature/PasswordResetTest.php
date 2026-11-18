<?php

namespace Tests\Feature;

use App\Mail\AdminResetPassword;
use App\Mail\ForgotPasswordMail;
use App\Mail\SendUsMail;
use App\Models\Admin;
use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function tokenFrom(string $mailClass, string $email): string
    {
        $url = null;
        Mail::assertQueued($mailClass, function ($mail) use ($email, &$url) {
            $url = $mail->url;
            return $mail->hasTo($email);
        });

        return substr($url, strrpos($url, '/') + 1);
    }

    // ------------------------------------------------------------------ customers

    public function test_customer_resets_their_password_with_the_emailed_link_once()
    {
        $user = User::factory()->create(['email' => 'ana@example.test', 'password' => Hash::make('old-password')]);

        $this->post(route('password.request'), ['email' => 'ana@example.test'])
            ->assertSessionHas('success', \App\Services\App\PasswordServices::SENT);
        $token = $this->tokenFrom(ForgotPasswordMail::class, 'ana@example.test');

        $this->assertSame(64, strlen($token));
        $this->assertSame(hash('sha256', $token), DB::table('password_resets')->value('token'), 'only a hash is stored');

        $this->get('/reset_password/' . $token)->assertOk()->assertDontSee('expired');
        $this->post(route('password.store'), ['token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        $this->post(route('password.store'), ['token' => $token, 'password' => 'again-123', 'password_confirmation' => 'again-123'])
            ->assertSessionHas('error');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password), 'a link works only once');
    }

    public function test_the_requesters_own_csrf_token_cannot_reset_someone_elses_password()
    {
        $victim = User::factory()->create(['email' => 'victim@example.test', 'password' => Hash::make('victim-password')]);

        // Like a browser: the attacker's own form carries their CSRF token.
        $this->get(route('password.forgot'));
        $csrf = session()->token();
        $this->post(route('password.request'), ['email' => 'victim@example.test', '_token' => $csrf]);

        $this->post(route('password.store'), ['token' => $csrf, 'password' => 'hacked-123', 'password_confirmation' => 'hacked-123'])
            ->assertSessionHas('error');
        $this->assertTrue(Hash::check('victim-password', $victim->fresh()->password));
    }

    public function test_unknown_emails_get_the_same_answer_and_no_email()
    {
        $this->post(route('password.request'), ['email' => 'nobody@example.test'])
            ->assertSessionHas('success', \App\Services\App\PasswordServices::SENT);
        Mail::assertNothingQueued();
    }

    public function test_links_expire_after_an_hour_and_a_new_request_replaces_the_old_link()
    {
        $user = User::factory()->create(['email' => 'ana@example.test', 'password' => Hash::make('old-password')]);
        $this->post(route('password.request'), ['email' => 'ana@example.test']);
        $first = $this->tokenFrom(ForgotPasswordMail::class, 'ana@example.test');

        Carbon::setTestNow(now()->addMinutes(61));
        $this->get('/reset_password/' . $first)->assertSee('Request already expires');
        $this->post(route('password.store'), ['token' => $first, 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertSessionHas('error');
        Carbon::setTestNow();

        $this->post(route('password.request'), ['email' => 'ana@example.test']);
        $this->assertSame(1, DB::table('password_resets')->where('email', 'ana@example.test')->count(), 'older links stop working');
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_forgot_password_is_rate_limited()
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('password.request'), ['email' => "x{$i}@example.test"])->assertRedirect();
        }
        $this->post(route('password.request'), ['email' => 'x6@example.test'])->assertStatus(429);
    }

    // ------------------------------------------------------------------ staff

    public function test_staff_reset_email_goes_to_the_staff_member_and_the_link_works()
    {
        $admin = $this->makeAdmin(['email' => 'staff@coda.test', 'password' => Hash::make('old-password')]);

        $this->post(route('admin.request.password'), ['email' => 'staff@coda.test'])
            ->assertSessionHas('success', \App\Services\Admin\Auth\PasswordServices::SENT);
        $token = $this->tokenFrom(AdminResetPassword::class, 'staff@coda.test');
        Mail::assertNotQueued(AdminResetPassword::class, fn ($m) => $m->hasTo('to@example.com'));

        $this->get('/admin/reset_password/' . $token)->assertOk()
            ->assertSee('name="token" value="' . $token . '"', false)->assertDontSee('expired');

        $this->post(route('admin.accountreset.password'), ['token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])
            ->assertRedirect(route('admin.login'))->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-password', $admin->fresh()->password));
    }

    public function test_staff_password_cannot_be_reset_with_the_requesters_csrf_token()
    {
        $admin = $this->makeAdmin(['email' => 'owner@coda.test', 'password' => Hash::make('owner-password')]);

        $this->get(route('admin.forget.password'));
        $this->post(route('admin.request.password'), ['email' => 'owner@coda.test', '_token' => session()->token()]);
        $this->post(route('admin.accountreset.password'), ['token' => session()->token(), '_token' => session()->token(), 'password' => 'hacked-123', 'password_confirmation' => 'hacked-123'])
            ->assertSessionHas('error');
        $this->post(route('admin.accountreset.password'), ['_token' => session()->token(), 'password' => 'hacked-123', 'password_confirmation' => 'hacked-123'])
            ->assertSessionHasErrors('token');

        $this->assertTrue(Hash::check('owner-password', $admin->fresh()->password));
        $this->post(route('admin.request.password'), ['email' => 'nobody@coda.test'])
            ->assertSessionHas('success', \App\Services\Admin\Auth\PasswordServices::SENT);
    }

    // ------------------------------------------------------------------ contact form

    public function test_contact_messages_go_to_the_store_not_to_the_address_typed_in()
    {
        $this->seedStoreBasics();
        GeneralSetting::query()->update(['site_email' => 'hello@coda.test']);

        $this->post(route('sendMessage'), ['email' => 'someone@example.test', 'content' => 'Do you ship to Cebu?'])
            ->assertSessionHas('success');

        Mail::assertQueued(SendUsMail::class, fn ($m) => $m->hasTo('hello@coda.test') && !$m->hasTo('someone@example.test')
            && $m->senderEmail === 'someone@example.test');

        $mail = new SendUsMail('someone@example.test', 'Do you ship to Cebu?');
        $mail->build();
        $this->assertTrue($mail->hasReplyTo('someone@example.test'));
    }
}
