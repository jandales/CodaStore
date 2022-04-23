<?php 

namespace App\Services;

use Carbon\Carbon;
use App\Models\Order;


class AdminOrderServices
{
    public function updateStatus(Order $order)
    {
        $order->status = 'shipped';  
        $order->shipped_at = now();    
        $order->save();
        return $order;
    }
    
    public function deliver()
    {
        $orders = Order::where('status','shipped')->get();
        $currentDate = Carbon::now();

        foreach($orders as $order)
        {
           $shipped_at =  Carbon::parse($order->created_at);  
           if($currentDate->diffInDays($shipped_at) > 1)
           {               
                $order->delivered_at = now();
                $order->status = "delivered";
                $order->save();
           }      
        }
    }
       
}