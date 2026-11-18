<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
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

        if(count($admin) == 0) return redirect()->route('admin.register');

        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $now = Carbon::now();
        $startData = '2022-02-11 11:50:52';
        $orders = Order::with('user', 'items', 'couponUsed')
            ->whereBetween('created_at', [$startData, $now->copy()->endOfWeek()])
            ->latest()
            ->take(8)
            ->get();

        // Income per month for the current year (cancelled and returned orders excluded).
        $paid = Order::whereYear('created_at', $now->year)
            ->whereNotIn('status', ['cancelled', 'returned']);
        $monthly = array_fill(1, 12, 0.0);
        foreach ((clone $paid)->get(['created_at', 'gross_total']) as $order) {
            $monthly[(int) $order->created_at->format('n')] += (float) $order->gross_total;
        }

        $thisMonth = $monthly[(int) $now->format('n')];
        $lastMonth = (int) $now->format('n') > 1
            ? $monthly[(int) $now->format('n') - 1]
            : (float) Order::whereYear('created_at', $now->year - 1)->whereMonth('created_at', 12)
                ->whereNotIn('status', ['cancelled', 'returned'])->sum('gross_total');
        $incomeChange = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : null;

        $threshold = \App\Models\GeneralSetting::lowStockThreshold();

        return view('admin.index')->with([
            'orders'          => $orders,
            'order_count'     => Order::count(),
            'orders_this_month' => Order::whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)->count(),
            'in_transit'      => Order::CountByStatus('shipped'),
            'to_ship'         => Order::CountByStatus('confirmed'),
            'completed_count' => Order::CountByStatus('completed'),
            'returned_count'  => Order::CountByStatus('returned'),
            'cancelled_count' => Order::CountByStatus('cancelled'),
            'customer_count'  => User::count(),
            'product_count'   => Product::count(),
            'monthly_income'  => $monthly,
            'year_income'     => array_sum($monthly),
            'income_change'   => $incomeChange,
            'admin'           => auth()->guard('admin')->user(),
            'low_stock'       => \App\Services\Products\LowStock::items($threshold, 6),
            'low_stock_count' => \App\Services\Products\LowStock::items($threshold)->count(),
            'stock_to_review' => Product::where('variant_stock_reviewed', false)->count(),
            'low_stock_threshold' => $threshold,
        ]);
    }
}
