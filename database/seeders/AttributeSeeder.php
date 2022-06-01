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
        $attributes = ['color', 'size'];
        
        foreach($attributes as $attribute)
        {
           Attribute::factory()->create([
                'name' => $attribute,
                'slug' => slug($attribute)
           ]);
        }
    }
}
