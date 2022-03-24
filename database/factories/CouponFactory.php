<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique(),
            'description' => $this->faker->unique()->safeEmail(),
            'discount_type' => 'percent-disc',
            'include_product_id' => null,
            'exclude_product_id' => null,
            'min_amount' => 100,
            'max_amount' => 500,
            'limit_per_coupon' => 100,
            'limit_to_xitems' => 100,
            'limit_per_user'=> 100,
            'expire_at' => null,
        ];
    }
}
