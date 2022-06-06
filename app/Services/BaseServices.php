<?php

namespace App\Services;

class BaseServices 
{
    public $app_perpage;
    public $shop_perpage;

    public function __construct()
    {
        $this->app_perpage =  config('setting.app.perpage');
        $this->shop_perpage =  config('setting.shop.perpage');
    }
}