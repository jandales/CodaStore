<?php

namespace Database\Factories;

use App\Models\GeneralSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeneralSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeneralSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'site_name' => 'company name',
            'tag_line' => 'tag line',
            'site_url' => 'example.com',
            'site_email' => 'example@email.com',
            'timezone' =>  null,
            'date_format' => 'F j, Y',
            'time_format' => '2:37 pm',
        ];
    }
}
