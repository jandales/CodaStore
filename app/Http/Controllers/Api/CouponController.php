<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FrontCouponServices;
use App\Http\Requests\CouponCodeRequest;

class CouponController extends Controller
{
    private $services;

    public function __construct(FrontCouponServices $services)
    {
        $this->services = $services;
    }

    public function index(CouponCodeRequest $request)
    {
        return $this->services->index($request);        
    }

    public function active() 
    {
       return $this->services->active();       
    }
 
    public function remove($id)
    {         
       return $this->services->remove($id);
    }

    public function resetProductDiscount()
    {
        return $this->services->resetProductDiscount();
    }
}
