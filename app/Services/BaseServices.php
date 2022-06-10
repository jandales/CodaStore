<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class BaseServices 
{
    public $app_perpage;
    public $shop_perpage;
    public $user;

    public function __construct()
    {
      
      
        $this->app_perpage =  config('setting.app.perpage');
        $this->shop_perpage =  config('setting.shop.perpage');

        Self::get_User();
    }

    public function get_User()
    {
        $this->user =  Auth::guard('admin')->user();
    }
}