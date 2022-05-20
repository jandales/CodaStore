<?php 

namespace App\Services;


use App\Models\Order;
use App\Models\Product;

use Illuminate\Http\Request;


class OrderServices
{
   
    public function listOrders($status)
    {
        return Order::ByAuthUser()->when($status, function ($query) use ($status) {
            if ($status != 'all') $query->where('status', $status);                                                    
        })->get();             
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
        $order->status = "canceled";
        $order->save();
        
    }

       
}