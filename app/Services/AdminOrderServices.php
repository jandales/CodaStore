<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;


class AdminOrderServices
{

    public $COMPLETED = 'completed';
    public $TOSHIP = Order::STATUS_CONFIRMED;
    public $TORECIEVE = Order::STATUS_SHIPPED;
    private $CANCELLED = Order::STATUS_CANCELLED;
    private $perpage;

    public function __construct()
    {
        $this->perpage = config('setting.app.perpage');
    }


    public function list($status = null)
    {
        $orders = Order::with('user', 'items', 'payment')->when($status, function($query) use ($status) {
                            return $query->where('status', $status);
                        })->paginate($this->perpage);

        return $orders;
    }


    public function updateStatus(Order $order)
    {
        $status = request()->status;

        // Cancelling also restores stock, gives the coupon back, refunds and emails the customer.
        if ($status == Order::STATUS_CANCELLED) {
            return app(\App\Services\Orders\CancelOrder::class)->handle($order);
        }

        if ($status == Order::STATUS_SHIPPED) {
            $order->shipped_at = now();
            $order->courier = request()->courier;
            $order->tracking_number = trim((string) request()->tracking_number);
        }

        if ($status == Order::STATUS_COMPLETED) {
            $order->delivered_at = now();

            // Cash on delivery: the courier collected the cash when it was delivered.
            $payment = $order->payment()->first();
            if ($payment && $payment->isCashOnDelivery() && $payment->status === \App\Models\OrderPayment::STATUS_PENDING) {
                $payment->update(['status' => \App\Models\OrderPayment::STATUS_PAID, 'collected_at' => now()]);
            }
        }

        $order->status = $status;
        $order->save();

        if ($status == Order::STATUS_SHIPPED) {
            app(\App\Services\OrderNotifier::class)->shipped($order);
        }

        return $order;
    }




    public function deliver()
    {
        $orders = Order::where('status', $this->TORECIEVE)->get();
        $currentDate = Carbon::now();

        foreach($orders as $order)
        {
           $shipped_at =  Carbon::parse($order->shipped_at ?? $order->created_at);
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

    public function destroy(Order $order)
    {
       return $order->delete();
        // $order->billing->delete();
        // $order->shipping->delete();
        // $order->payment->delete();

        // foreach($order->items as $item)
        // {
        //     $item->delete();
        // }

        // $order->delete();
    }


}