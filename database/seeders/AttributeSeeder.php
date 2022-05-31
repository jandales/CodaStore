<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attributes = ['size', 'color'];
        
        foreach($attributes as $attribute)
        {
           Attribute::factory()->create([
                'name' => $attribute,
                'slug' => slug($attribute)
           ]);
        }
    }
}
