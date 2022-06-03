<?php 

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Models\OrderBilling;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Models\OrderShipping;
use App\Models\PaymentDetail;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;

class PlaceOrderServices {
    
    private $provider = 'card';
    private $order;
    
    private function orderNumber()
    {      
        return date('Y')."".now()->timestamp;         
    }
   
    public function storeOrder(Request $request)
    {
       
        $cart = Cart::ByUser()->first(); 
        $shipping_method = ShippingMethod::find(session()->get('shipping_method')['id']); 
        $shipping_charge = $shipping_method->amount;

        $gross_total = (double)$cart->grandTotal();
        $tax = tax($gross_total);
        $gross_total += $tax; 

        return DB::transaction(function () use ($cart,$shipping_method, $shipping_charge, $gross_total,$tax,$request){

            $this->order = Order::create([    
                'order_number' => Self::orderNumber(),
                'user_id' => auth()->user()->id,
                'shipping_method_id' => $shipping_method->id,
                'gross_total' => $gross_total, 
                'net_total' => $cart->total,
                'shipping_charge' => $shipping_charge,               
                'status' => 'to-ship',
                'num_items_sold' => $cart->items->count(),
                'tax_total' => $tax,
                'coupon_id' => $cart->coupon_id,
                'coupon_amount' => $cart->discount,        
            ]);           
           
           
            Self::storeOrderShipping();
           
            Self::storeOrderBilling();
    
            Self::storeOrderItem($cart->items);

            Self::storeOrderPayment($gross_total);  

            Self::updateCouponUsage($cart->coupon_id);
    
            Self::deleteCheckoutSession();
    
            return $this->order; 
        });
        
          
       
    }

    private function storeOrderBilling()
    {
        return OrderBilling::create([
            'firstname' => request()->input('is_new_billing') == 1 ?  request()->input('billing_firstname') : session()->get('shipping_address')['firstname'],
            'lastname' => request()->input('is_new_billing') == 1 ?  request()->input('billing_lastname') : session()->get('shipping_address')['lastname'],
            'email' =>  session()->get('email'),
            'street' => request()->input('is_new_billing') == 1 ? request()->input('billing_street') : session()->get('shipping_address')['street'],
            'city' => request()->input('is_new_billing') == 1 ?  request()->input('billing_city') : session()->get('shipping_address')['city'],
            'phone' =>  request()->input('is_new_billing') == 1 ? request()->input('billing_phone') :session()->get('shipping_address')['phone'],
            'country' =>request()->input('is_new_billing') == 1 ? request()->input('billing_country') : session()->get('shipping_address')['country'],
            'region' => request()->input('is_new_billing') == 1 ?  request()->input('billing_region') : session()->get('shipping_address')['region'],
            'zipcode' => request()->input('is_new_billing') == 1 ? request()->input('billing_zipcode') :session()->get('shipping_address')['zipcode'],
            'order_id' =>  $this->order->id,      
        ]);
    }


    private function storeOrderShipping()
    {
        OrderShipping::create([
            'firstname' => session()->get('shipping_address')['firstname'],
            'lastname' => session()->get('shipping_address')['lastname'],
            'street' => session()->get('shipping_address')['street'],
            'city' => session()->get('shipping_address')['city'],
            'phone' =>session()->get('shipping_address')['phone'],
            'country' => session()->get('shipping_address')['country'],
            'region' => session()->get('shipping_address')['region'],
            'zipcode' => session()->get('shipping_address')['zipcode'],
            'order_id' => $this->order->id,      
        ]);
    }

    
    private function storeOrderPayment($amount)
    { 
        return OrderPayment::create([
            'order_id' => $this->order->id,
            'amount' => $amount,
            'provider' => $this->provider,
            'reference_number' => request()->input('card_number'),
            'status' => 'paid',
        ]);
    }
       

    private function storeOrderItem($items)
    {
        foreach($items as $item)
        {
            OrderProduct::create([
                'order_id' => $this->order->id,
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->product->regular_price,
                'properties' => $item->properties,
            ]); 
              
            Stock::where('product_id', $item->product_id)->decrement('qty', $item->qty);
            $item->delete();
        }
    }

    private function updateCouponUsage($coupon_id)
    {
        $coupon = Coupon::find($coupon_id);
        if(empty($coupon)) return;
        $coupon->usage += 1;
        $coupon->save();
    }

    private function deleteCheckoutSession()
    {
        session()->forget('email');
        session()->forget('shipping_address');
        session()->forget('shipping_method');
        session()->forget('shipping_charge');
      
    }
}