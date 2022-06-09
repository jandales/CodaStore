<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $TYPE = '.webp';
        $name = $this->faker->word();
        return [  
            'name' => $this->faker->word,
            'category_id' => rand(1,3),
            'slug' => slug($name),        
            'short_description' => $this->faker->sentence(),            
            'long_description' => $this->faker->sentence(),
            'imagePath' => '/img/products/default/' . rand(1,20) . $TYPE,
            'sku' => $this->faker->phoneNumber(),  
            'barcode' => $this->faker->phoneNumber(),      
            'tags' => $name,
            'sale_price' => 399,
            'regular_price' => 500,
            'status' => 1,
            'admin_id' => 1,
            'featured' => rand(0,1),
        ];

        // $users = factory(App\User::class, 3)
        //    ->create()
        //    ->each(function ($user) {
        //         $user->posts()->save(factory(App\Post::class)->make());
        //     });

       
    }
}
