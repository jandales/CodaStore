<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition()
    {
        return [
            // Carts are looked up by the "cart-id" cookie value.
            'cart_id' => $this->faker->unique()->numberBetween(100000000, 999999999),
            'expired_at' => now()->addDays(7),
        ];
    }
}
