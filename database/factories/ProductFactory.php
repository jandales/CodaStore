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
        return [
            'name' => $this->faker->name(),
            'category_id' => 1,            
            'slug_name' =>  slug($this->faker->name()),         
            'short_description' =>  $this->faker->paragraph(),
            'long_description' =>  $this->faker->paragraph(),
            'imagePath' =>   'img/products/default.jpg',
            'sku' =>  $this->faker->name(),
            'barcode' => $this->faker->randomNumber(12),
            'sale_price' => $this->faker->randomNumber(5),
            'regular_price' =>  $this->faker->randomNumber(5),
            'tags' => $this->faker->tags, 
            'status' => 'draft',
            'istaxeble' => 0,       
        ];

        // $users = factory(App\User::class, 3)
        //    ->create()
        //    ->each(function ($user) {
        //         $user->posts()->save(factory(App\Post::class)->make());
        //     });

       
    }
}
