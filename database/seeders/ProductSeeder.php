<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Attribute;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Photo;

class ProductSeeder extends Seeder
{
   private $sizes = [10, 20, 30, 40, 50];
   private $colors = ['grey', 'black', 'white', 'red'];
   private $TYPE = '.webp';
   private $location = '/img/products/default/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $attributesCount = Attribute::get()->count(); 
        Product::factory(100)
        ->create()
        ->each(function($user) {
            Stock::factory()->create([
                'product_id' => $user->id,                
            ]);  
            $attribute_id = rand(1, 2);       
            ProductAttribute::factory()->create([
                'product_id' => $user->id,
                'attribute_id' => $attribute_id,
            ]);  
            $this->createAttributes($user->id,$attribute_id); 
        });
    }


    private function createAttributes($user_id, $attribute_id)
    {
        Self::createSizes($user_id, $attribute_id);
        Self::createColor($user_id, $attribute_id);
        Self::createPhotos($user_id);

    }

    private function createSizes($user_id, $attribute_id)
    {
        foreach($this->sizes as $size)
        {            
            ProductVariant::factory()->create([
                'product_id' => $user_id,
                'attribute_id' => $attribute_id,
                'name' => $size,
            ]);            
        }
      
    }

    private function createColor($user_id, $attribute_id)
    {       
        foreach($this->colors as $color)
        {
            ProductVariant::factory()->create([
                'product_id' => $user_id,
                'attribute_id' => $attribute_id,
                'name' => $color,
            ]);
        }
    }

    private function createPhotos($user_id)
    {
        $location = '/img/products/default/';
        for($i = 0; $i < 5; $i++)
        {
            Photo::factory()->create([
                'product_id' => $user_id,
                'path' => $location . rand(1,20) . $this->TYPE,  
                'location' => $location,
            ]);
        }
        
    }

  
}
