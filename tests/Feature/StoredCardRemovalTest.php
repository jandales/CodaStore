<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StoredCardRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_cards_table_is_gone()
    {
        $this->assertFalse(Schema::hasTable('user_payment_options'));
    }

    public function test_migration_keeps_only_the_last_four_digits_of_old_card_payments()
    {
        $order = Order::factory()->create();
        $card = DB::table('order_payments')->insertGetId(['order_id' => $order->id, 'provider' => 'card', 'reference_number' => '4111-1111-1111-1234', 'amount' => '500', 'status' => 'paid']);
        $paypal = DB::table('order_payments')->insertGetId(['order_id' => $order->id, 'provider' => 'paypal', 'reference_number' => 'CAP-123', 'amount' => '500', 'status' => 'paid']);

        (new \RemoveStoredCardData)->up();

        $this->assertSame('card ending 1234', DB::table('order_payments')->where('id', $card)->value('reference_number'));
        $this->assertSame('CAP-123', DB::table('order_payments')->where('id', $paypal)->value('reference_number'));
    }

    public function test_saved_card_pages_no_longer_exist()
    {
        $this->signInCustomer();

        $this->get('/account/payment-option')->assertNotFound();
        $this->get('/account/payment-option/create')->assertNotFound();
    }

    public function test_account_menu_has_no_payment_methods_link()
    {
        $this->seedStoreBasics();
        $this->signInCustomer();

        $this->get('/account')->assertOk()->assertDontSee('Payment methods');
    }
}
