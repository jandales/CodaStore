<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_cannot_place_an_unpaid_order()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/checkout/placeorder', ['card_number' => '4111111111111111'])
            ->assertStatus(422)
            ->assertJson(['message' => 'Online payment is required. Please complete checkout on the website.']);
        $this->assertSame(0, Order::count());
    }

    public function test_api_lists_only_the_signed_in_users_orders()
    {
        $me = User::factory()->create();
        $mine = Order::factory()->create(['user_id' => $me->id]);
        Order::factory()->create(); // someone else's
        Sanctum::actingAs($me);

        $this->getJson('/api/user/orders')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $mine->id);
    }

    public function test_api_cannot_view_or_cancel_someone_elses_order()
    {
        $other = Order::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/user/orders/' . $other->id)->assertNotFound();
        $this->patchJson('/api/user/orders/cancel/' . $other->id)->assertNotFound();
        $this->assertSame(Order::STATUS_CONFIRMED, $other->fresh()->status);
    }

    public function test_api_cancel_uses_the_shared_cancellation_rules()
    {
        $me = User::factory()->create();
        $shipped = Order::factory()->create(['user_id' => $me->id, 'status' => Order::STATUS_SHIPPED]);
        Sanctum::actingAs($me);

        $this->patchJson('/api/user/orders/cancel/' . $shipped->id)->assertStatus(422);
        $this->assertSame(Order::STATUS_SHIPPED, $shipped->fresh()->status);
    }
}
