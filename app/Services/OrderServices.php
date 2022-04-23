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

class OrderServices
{
    public function storeOrder(Request $request)
    {
        $cart = Cart::ByUser()->first(); 
        $shipping_method = ShippingMethod::find(session()->get('shipping_method')['id']); 
        $shipping_charge = $shipping_method->amount;

        $gross_total = (double)$cart->grandTotal();
        $tax = tax($gross_total);
        $gross_total += $tax; 
          
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'shipping_method_id' => $shipping_method->id,
            'gross_total' => $gross_total, 
            'net_total' => $cart->total,
            'shipping_charge' => $shipping_charge,               
            'status' => 'pending',
            'num_items_sold' => $cart->items->count(),
            'tax_total' => $tax,
            'coupon_id' => $cart->coupon_id,
            'coupon_amount' => $cart->discount,        
        ]);
        
        Self::storeOrderPayment($request, $order);
       
        Self::storeOrderShipping($request, $order);
       
        Self::storeOrderBilling($request, $order);

        Self::storeOrderItem($cart->items, $order);

        Self::storeOrderPaymentDetails($order, $gross_total, 'Stripe', 'completed');      

        Self::updateCouponUsage($cart->coupon_id);

        Self::deleteCheckoutSession();

        return $order;
    }

    private function storeOrderBilling(Request $request, $order)
    {
        return OrderBilling::create([
            'firstname' => $request->is_new_billing == 1 ?  $request->billing_firstname : session()->get('shipping_address')['firstname'],
            'lastname' => $request->is_new_billing == 1 ?  $request->billing_lastname : session()->get('shipping_address')['lastname'],
            'email' =>  session()->get('email'),
            'street' => $request->is_new_billing == 1 ? $request->billing_street : session()->get('shipping_address')['street'],
            'city' => $request->is_new_billing == 1 ?  $request->billing_city : session()->get('shipping_address')['city'],
            'phone' =>  $request->is_new_billing == 1 ? $request->billing_phone :session()->get('shipping_address')['phone'],
            'country' => $request->is_new_billing == 1 ? $request->billing_country : session()->get('shipping_address')['country'],
            'region' => $request->is_new_billing == 1 ?  $request->billing_region : session()->get('shipping_address')['region'],
            'zipcode' => $request->is_new_billing == 1 ?  $request->billing_zipcode :session()->get('shipping_address')['zipcode'],
            'order_id' =>  $order->id,      
        ]);
    }

    private function storeOrderPaymentDetails($order, $amount, $provider, $status)
    {
        return PaymentDetail::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'provider' => $provider,
            'status' => $status,
        ]);
    }

    private function storeOrderShipping(Request $request, $order)
    {
        return OrderShipping::create([
            'firstname' => session()->get('shipping_address')['firstname'],
            'lastname' => session()->get('shipping_address')['lastname'],
            'street' => session()->get('shipping_address')['street'],
            'city' => session()->get('shipping_address')['city'],
            'phone' =>session()->get('shipping_address')['phone'],
            'country' => session()->get('shipping_address')['country'],
            'region' => session()->get('shipping_address')['region'],
            'zipcode' => session()->get('shipping_address')['zipcode'],
            'order_id' => $order->id,      
        ]);
    }

    
    private function storeOrderPayment(Request $request, $order)
    {
        return OrderPayment::create([
            'card_name' => $request->card_name,
            'card_number' => $request->card_number,
            'card_expire_date' => $request->card_expire_date,
            'card_cvc' => $request->card_cvc,
            'order_id' => $order->id,
        ]);
    }


    private function storeOrderItem($items, $order)
    {
        foreach($items as $item)
        {
            OrderProduct::create([
                'order_id' => $order->id,
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