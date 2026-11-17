<?php

namespace Tests\Feature;

use App\Mail\Admin\LowStockDigestMail;
use App\Models\Admin;
use App\Models\GeneralSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_published_products_at_or_below_the_threshold()
    {
        $this->createProduct(3, false, ['name' => 'nearly gone tee']);
        $this->createProduct(0, false, ['name' => 'sold out cap']);
        $this->createProduct(40, false, ['name' => 'plenty jacket']);
        $this->createProduct(1, false, ['name' => 'draft scarf', 'status' => 0]);
        $this->signInAdmin();

        $response = $this->get(route('admin.dashboard'))->assertOk();

        $names = $response->viewData('low_stock')->pluck('name')->all();
        $this->assertSame(['sold out cap', 'nearly gone tee'], $names, 'lowest first, drafts and well-stocked excluded');
        $response->assertSee('Low stock')->assertSee('Sold out')->assertSee('3 left');
    }

    public function test_threshold_is_a_setting()
    {
        $this->createProduct(8, false, ['name' => 'eight left']);
        GeneralSetting::first()->update(['low_stock_threshold' => 10]);
        $this->signInAdmin();

        $this->assertSame(['eight left'], $this->get(route('admin.dashboard'))->viewData('low_stock')->pluck('name')->all());
    }

    public function test_admin_can_change_the_threshold_in_settings()
    {
        $this->seedStoreBasics();
        $settings = GeneralSetting::first();
        $this->signInAdmin();

        $this->put(route('admin.setting.general.update', [$settings]), [
            'site_name' => 'Coda', 'tag_line' => 'Clothing', 'site_url' => 'https://coda.test',
            'site_email' => 'store@coda.test', 'timezone' => 'Asia/Manila', 'date_format' => 'F j, Y', 'time_format' => 'g:i a',
            'low_stock_threshold' => 12,
        ])->assertSessionHasNoErrors();

        $this->assertSame(12, $settings->fresh()->low_stock_threshold);
        $this->assertSame(12, GeneralSetting::lowStockThreshold(), 'cached value is refreshed after saving');
    }

    public function test_inventory_has_a_low_stock_filter()
    {
        $this->createProduct(2, false, ['name' => 'two left']);
        $this->createProduct(50, false, ['name' => 'fifty left']);
        $this->signInAdmin();

        $this->get(route('admin.inventory.filter', ['low-stock']))
            ->assertOk()
            ->assertSee('two left')
            ->assertDontSee('fifty left');
    }

    public function test_daily_digest_emails_the_store()
    {
        Mail::fake();
        $this->createProduct(2, false, ['name' => 'two left']);
        GeneralSetting::first()->update(['site_email' => 'store@coda.test']);

        $this->artisan('store:low-stock-digest')->assertExitCode(0);

        Mail::assertSent(LowStockDigestMail::class, function ($mail) {
            return $mail->hasTo('store@coda.test') && $mail->products->pluck('name')->all() === ['two left'];
        });
    }

    public function test_digest_falls_back_to_administrators_and_skips_when_nothing_is_low()
    {
        Mail::fake();
        $this->createProduct(50, false);
        GeneralSetting::first()->update(['site_email' => null]);
        Admin::factory()->create(['role' => 1, 'email' => 'boss@coda.test']);

        $this->artisan('store:low-stock-digest')->assertExitCode(0);
        Mail::assertNothingSent();

        $this->createProduct(1, false, ['name' => 'one left']);
        $this->artisan('store:low-stock-digest')->assertExitCode(0);
        Mail::assertSent(LowStockDigestMail::class, fn ($mail) => $mail->hasTo('boss@coda.test'));
    }

    public function test_digest_is_scheduled_daily()
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events());

        $this->assertTrue($events->contains(fn ($e) => str_contains($e->command, 'store:low-stock-digest') && $e->expression === '0 8 * * *'));
    }

    public function test_shoppers_see_only_n_left_at_the_threshold()
    {
        $product = $this->createProduct(5, false);
        $this->assertSame('Only 5 left', $product->minQty());

        $plenty = $this->createProduct(6, false);
        $this->assertNull($plenty->minQty());
    }
}
