<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
      
    public function index()
    {        
       // count if theres record in admin table     
        $admin = Admin::all();

        if(session()->has('adminLogined')) return redirect()->route('dashboard');

        if(count($admin) == 0) return redirect()->route('admin-register');

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {    
        $startData = '2022-02-11 11:50:52'; 
        $orders = Order::with('user','items','couponUsed')->whereBetween('created_at', [$startData , Carbon::now()->endOfWeek()])->get(); 

        return view('admin.index')->with([
            'orders' => $orders, 
            'completed_count' =>  Order::CountByStatus('completed'),
            'returned_count' => Order::CountByStatus('returened'),
            'cancelled_count' =>  Order::CountByStatus('cancelled'),
            'customer_count' =>  User::get()->count(),
        ]);
    }
}
