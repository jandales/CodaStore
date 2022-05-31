<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{  
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = ["hoddie","jacket","others"];   
        $path =  "/img/collection/default";
      
        foreach($names as $name){
            Category::factory()->create([
                'name' => $name,
                'slug' => slug($name), 
                'image' =>  $path . "/" . $name . '.jpg',
            ]);
        } 

    }
}
