<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $net = $this->faker->numberBetween(500, 5000);

        return [
            'user_id' => User::factory(),
            'order_number' => (int) (date('Y') . $this->faker->unique()->numberBetween(100000, 999999)),
            // Shipping method names are unique, so reuse one when it exists.
            'shipping_method_id' => fn () => optional(ShippingMethod::first())->id ?? ShippingMethod::factory()->create()->id,
            'shipping_charge' => 150,
            'num_items_sold' => 1,
            'tax_total' => 0,
            'gross_total' => $net,
            'net_total' => $net,
            'coupon_id' => null,
            'coupon_amount' => 0,
            'status' => 'confirmed',
        ];
    }

    public function status(string $status)
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
