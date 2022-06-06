<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $orders = Order::whereBetween('created_at', [$startData , Carbon::now()->endOfWeek()])->get();         
        return view('admin.index')->with([ 'orders' => $orders, 
            'completed_count' =>  Order::CompletedCount(),
            'returned_count' => Order::Returnedcount(),
            'cancelled_count' =>  Order::CancelledCount(),
            'customer_count' =>  User::get()->count(),
        ]);
    }
}
