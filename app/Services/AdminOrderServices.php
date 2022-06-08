<?php 

namespace App\Services;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;


class AdminOrderServices
{

    public $COMPLETED = 'completed';
    public $TOSHIP = 'to-ship';
    public $TORECIEVE = 'to-receive';
    private $CANCELLED = 'cancelled';
    private $perpage;

    public function __construct()
    {
        $this->services = config('settings.app.perpage');
    }


    public function list($status = null)
    {  
        $orders = Order::with('user', 'items')->when($status, function($query) use ($status) {
                            return $query->where('status', $status);
                        })->paginate($this->perpage);

        return $orders;
    }


    public function updateStatus(Order $order)
    {
        $status = request()->status;
       
        if ($status == 'shipped')
        {
            $order->shipped_at = now(); 
        }           
        
        if($status == 'completed')
        {
            $order->delivered_at = now();
        }

        if ($status == 'completed') 
        {
            $order->cancelled_at = now();
        }
        
        // if ($status == 'returned')            
        // {
        //     $order->returned_at = now();
        // }

        $order->status = $status;         
        $order->save();

        return $order;
    }

    
    
    
    public function deliver()
    {
        $orders = Order::where('status', $this->TORECIEVE)->get();
        $currentDate = Carbon::now();

        foreach($orders as $order)
        {
           $shipped_at =  Carbon::parse($order->created_at);  
           if($currentDate->diffInDays($shipped_at) > 1)
           {               
                $order->delivered_at = now();
                $order->status = $this->COMPLETED;
                $order->save();
           }      
        }
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;    
        $orders =  Order::Search($keyword)->paginate($this->perpage);
        return ['orders' => $orders, 'keyword' => $keyword];
    }
       
}