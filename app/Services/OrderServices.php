<?php 

namespace App\Services;


use App\Models\Order;
use App\Models\Product;

use Illuminate\Http\Request;


class OrderServices
{
    public $COMPLETED = 'completed';
    public $TOSHIP = Order::STATUS_CONFIRMED;
    public $TORECIEVE = Order::STATUS_SHIPPED;
    private $CANCELLED = Order::STATUS_CANCELLED;

    public function listOrders($status)
    {
       return Order::with('items', 'items.product')->where('user_id', auth()->user()->id)->when($status, function ($query) use ($status) {

            if ($status != 'all') $query->where('status', $status);    

        })->paginate(config('setting.app.perpage')); 

    

        // return Order::ByAuthUser()->when($status, function ($query) use ($status) {
        //     if ($status != 'all') $query->where('status', $status);                                                    
        // })->paginate(config('setting.app.perpage'));             
    }

    public function review(Product $product)
    {
        $review = [];       

        if($product->reviewby(auth()->user()))  

            $review =  auth()->user()->review($product);

        return $review;

    }

    public function cancel(Order $order)
    {
        return app(\App\Services\Orders\CancelOrder::class)->handle($order);
    }

       
}