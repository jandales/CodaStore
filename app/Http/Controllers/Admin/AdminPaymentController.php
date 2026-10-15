<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderPayment;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        $payments = OrderPayment::with('order')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('reference_number', 'like', '%' . $keyword . '%')
                    ->orWhere('provider', 'like', '%' . $keyword . '%')
                    ->orWhere('status', 'like', '%' . $keyword . '%')
                    ->orWhereHas('order', function ($q) use ($keyword) {
                        $q->where('order_number', 'like', '%' . $keyword . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.payments.index')->with([
            'payments' => $payments,
            'keyword' => $keyword,
        ]);
    }
}
