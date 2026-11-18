<?php

namespace Tests\Feature;

use App\Mail\Concerns\SendsInBackground;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\SendUsMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Order;
use App\Services\Emails\EmailQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** A mail server that always refuses the login. */
class RefusedLoginMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsInBackground;

    public function build()
    {
        throw new \Swift_TransportException('Failed to authenticate on SMTP server with username "shop" using 2 possible authenticators. Authenticator LOGIN returned Expected response code 235 but got code "535".');
    }
}

/**
 * Uses a real database queue and no test transaction, so "send after the save is committed"
 * behaves exactly as it does in the store.
 */
class BackgroundEmailsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        config(['queue.default' => 'database', 'mail.default' => 'array']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function sent(): array
    {
        return app('mailer')->getSwiftMailer()->getTransport()->messages()->all();
    }

    private function work(): void
    {
        Artisan::call('queue:work', ['--once' => true, '--queue' => 'emails', '--sleep' => 0]);
    }

    private function waiting(): int
    {
        return DB::table('jobs')->where('queue', 'emails')->count();
    }

    public function test_status_emails_wait_for_the_worker_instead_of_the_page()
    {
        $order = $this->createPaidOrder();
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => 'LBC1'])
            ->assertRedirect();

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        $this->assertSame(1, $this->waiting(), 'queued on the emails queue');
        $this->assertCount(0, $this->sent(), 'nothing sent while the page waited');

        $this->work();

        $this->assertSame(0, $this->waiting());
        $this->assertCount(1, $this->sent());
        $this->assertArrayHasKey('ana@example.test', $this->sent()[0]->getTo());
    }

    public function test_emails_queued_in_a_save_that_is_rolled_back_are_never_sent()
    {
        try {
            DB::transaction(function () {
                Mail::to('store@coda.test')->queue(new SendUsMail('visitor@example.test', 'Hello'));
                throw new \RuntimeException('save failed');
            });
        } catch (\RuntimeException $e) {
        }
        $this->assertSame(0, $this->waiting());

        DB::transaction(fn () => Mail::to('store@coda.test')->queue(new SendUsMail('visitor@example.test', 'Hello')));
        $this->assertSame(1, $this->waiting(), 'queued once the save is committed');
    }

    public function test_a_failing_mail_server_is_tried_three_times_then_listed_with_a_plain_reason()
    {
        Mail::to('ana@example.test')->queue(new RefusedLoginMail);

        $this->work();                                  // try 1
        $this->assertSame(1, $this->waiting(), 'waits 1 minute before the next try');
        $this->work();
        $this->assertSame(0, DB::table('failed_jobs')->count(), 'not tried again before the delay');

        Carbon::setTestNow(now()->addSeconds(61));
        $this->work();                                  // try 2
        Carbon::setTestNow(now()->addSeconds(301));
        $this->work();                                  // try 3
        $this->assertSame(0, $this->waiting());
        $this->assertSame(1, DB::table('failed_jobs')->where('queue', 'emails')->count());

        $failed = EmailQueue::failed()->first();
        $this->assertSame('RefusedLoginMail', $failed->type);
        $this->assertSame(['ana@example.test'], $failed->to);
        $this->assertSame('The mail server refused the login (check the mail username and password)', $failed->reason);
        $this->assertStringContainsString('Failed to authenticate', $failed->detail);
    }

    public function test_an_email_for_a_deleted_order_fails_once_and_says_why()
    {
        $order = Order::factory()->create();
        Mail::to('ana@example.test')->queue(new OrderCancelledMail($order));
        $order->forceDelete();

        $this->work();

        $failed = EmailQueue::failed()->first();
        $this->assertSame('Order cancelled', $failed->type);
        $this->assertSame('The order or customer was deleted before the email was sent', $failed->reason);
        $this->assertNull($failed->order);
    }

    public function test_failed_emails_page_retries_and_deletes()
    {
        Mail::to('ana@example.test')->queue(new SendUsMail('visitor@example.test', 'one'));
        Mail::to('ben@example.test')->queue(new SendUsMail('visitor@example.test', 'two'));
        foreach (DB::table('jobs')->get() as $job) {
            DB::table('failed_jobs')->insert(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'connection' => 'database', 'queue' => 'emails',
                'payload' => $job->payload, 'exception' => "Swift_TransportException: Connection could not be established with host smtp.test :stream_socket_client(): unable to connect\n#0 /app/Foo.php(535): bar()", 'failed_at' => now()]);
        }
        DB::table('jobs')->delete();
        [$first, $second] = DB::table('failed_jobs')->orderBy('id')->get()->all();

        $this->signInAdmin();
        $this->get(route('admin.emails.failed'))->assertOk()
            ->assertSee('Contact form message')->assertSee('ana@example.test')->assertSee('ben@example.test')
            ->assertSee('Couldn&#039;t connect to the mail server', false)->assertDontSee('refused the login');

        $this->post(route('admin.emails.failed.retry', $first->id))->assertSessionHas('success');
        $this->assertSame(1, $this->waiting());
        $this->assertSame(1, DB::table('failed_jobs')->count());

        $this->delete(route('admin.emails.failed.destroy', $second->id))->assertSessionHas('success');
        $this->assertSame(0, DB::table('failed_jobs')->count());
        $this->assertSame(['email.deleted', 'email.retry'], ActivityLog::orderByDesc('id')->limit(2)->pluck('action')->all());

        $this->work();
        $this->assertCount(1, $this->sent(), 'the retried email goes out');

        $this->get(route('admin.emails.failed'))->assertSee('No failed emails');
        $this->post(route('admin.emails.failed.retry', 999))->assertNotFound();
    }

    public function test_retry_all()
    {
        foreach (['a@example.test', 'b@example.test'] as $to) {
            Mail::to($to)->queue(new SendUsMail('visitor@example.test', 'x'));
        }
        foreach (DB::table('jobs')->get() as $job) {
            DB::table('failed_jobs')->insert(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'connection' => 'database', 'queue' => 'emails', 'payload' => $job->payload, 'exception' => 'Error', 'failed_at' => now()]);
        }
        DB::table('jobs')->delete();
        $this->signInAdmin();

        $this->post(route('admin.emails.failed.retry-all'))->assertSessionHas('success', '2 emails are being sent again.');
        $this->assertSame(2, $this->waiting());
        $this->assertSame(0, DB::table('failed_jobs')->count());
    }

    public function test_dashboard_warns_when_emails_are_stuck_or_failed()
    {
        $this->seedStoreBasics();
        Mail::to('ana@example.test')->queue(new SendUsMail('visitor@example.test', 'x'));
        $this->signInAdmin();
        $this->get(route('admin.dashboard'))->assertDontSee('waiting to send')->assertDontSee("couldn't be sent", false);

        DB::table('jobs')->update(['available_at' => now()->subMinutes(11)->getTimestamp()]);
        DB::table('failed_jobs')->insert(['uuid' => 'u-1', 'connection' => 'database', 'queue' => 'emails', 'payload' => DB::table('jobs')->value('payload'), 'exception' => 'Error', 'failed_at' => now()]);

        $this->get(route('admin.dashboard'))->assertSee('1 email waiting to send for over 10 minutes')
            ->assertSee("1 email couldn't be sent", false)->assertSee(route('admin.emails.failed'), false);

        $staff = $this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]);
        $this->signInAdmin($staff);
        $this->get(route('admin.dashboard'))->assertDontSee('waiting to send');
        $this->get(route('admin.emails.failed'))->assertForbidden();
    }

    public function test_every_store_email_is_sent_in_the_background_with_retries()
    {
        foreach (array_keys(EmailQueue::LABELS) as $class) {
            $this->assertTrue(is_subclass_of($class, ShouldQueue::class), $class);
            $props = (new \ReflectionClass($class))->getDefaultProperties();
            $this->assertSame(3, $props['tries'] ?? null, $class);
            $this->assertSame(30, $props['timeout'] ?? null, $class);
        }
        $this->assertSame('emails', (new \App\Notifications\VerifyEmailInBackground)->queue);
    }

    public function test_reasons_ignore_numbers_in_the_stack_trace()
    {
        $this->assertSame('Sending failed', EmailQueue::reason("RuntimeException: Something odd\n#0 /app/Mail.php(535): send()")[0]);
        $this->assertSame('The email address was rejected', EmailQueue::reason('Swift_TransportException: Expected response code 250 but got code "550", with message "550 5.1.1 User unknown"')[0]);
        $this->assertSame('The mail server took too long to answer', EmailQueue::reason('Illuminate\Queue\MaxAttemptsExceededException: App\Mail\SendUsMail has been attempted too many times or run too long.')[0]);
    }
}
