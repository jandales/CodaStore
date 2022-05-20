<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use App\Services\CartServices;
use App\Http\Requests\QuantityRequest;
use App\Http\Requests\CouponCodeRequest;

class CartController extends Controller
{
    private $services;

    public function __construct(CartServices $services)
    {
        $this->services = $services;
        
        $this->middleware(function ($request, $next) {  
            $this->services->deleteExpiredCart();
            return $next($request);
        });
    }     

    public function index()
    { 
        $result = $this->services->index();            
        return view('cart')->with(['cart' =>  $result->cart, 'shipping_methods' => $result->shipping_methods]);
    }

    public function store(QuantityRequest $request, Product $product)
    {        
       return $this->services->store($request, $product);       
    }

    public function createCart()
    {     
        return $this->services->createCart();
    }
  
    public function count()
    {       
        return $this->services->count();
    }

    public function update(Request $request, CartItem $cartitem)
    {       
        return $this->services->update($request, $cartitem);
    }

 
    public function destroy(CartItem $item)
    {     
        return $this->services->destroyCartItem($item);          
    } 

    public function couponActivate(CouponCodeRequest $request)
    {
        return $this->services->couponActivate($request); 
    }
   
    public function couponRemove()
    {  
        return $this->services->couponRemove();       
    }

    public function selectShippingMethod(ShippingMethod $shipping_method)
    { 
        return $this->services->selectShippingMethod($shipping_method);
    }
 
    public function getTotal()
    {
       return $this->services->total();
    }

    public function get_user_cart()
    {
       return $this->services->getCart();
    }
    
}
