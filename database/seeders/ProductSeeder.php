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
   private $attributesCount = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {       
        Product::factory(100)
        ->create()
        ->each(function($user) {
            Stock::factory()->create([
                'product_id' => $user->id,                
            ]);  
            $this-> createAllRelatedTable($user->id);
                  
        });
    }


    private function createAllRelatedTable($user_id)
    {
        Self::createAttributes($user_id);  
        Self::createVaraints($user_id);
        Self::createPhotos($user_id);
    }

    private function createAttributes($user_id)
    {
        for ($i=1; $i < $this->attributesCount + 1; $i++) { 
            ProductAttribute::factory()->create([
                'product_id' => $user_id,
                'attribute_id' => $i,
            ]);
        }
    }

    private function createVaraints($user_id)
    {
        
        for ($i=1; $i < $this->attributesCount + 1; $i++)
        { 
                   
           if($i == 1){
                foreach($this->colors as $color)
                {
                    ProductVariant::factory()->create([
                        'product_id' => $user_id,
                        'attribute_id' => $i,
                        'name' => $color,
                    ]);
                }    
           }         

            if($i == $this->attributesCount) {
                foreach($this->sizes as $size)
                {            
                    ProductVariant::factory()->create([
                        'product_id' => $user_id,
                        'attribute_id' => $i,
                        'name' => $size,
                    ]);            
                }
            }

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
