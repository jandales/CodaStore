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
        $method = in_array($request->method, ['paypal', 'cod'], true) ? $request->method : null;
        $status = $request->status ? (string) $request->status : null;

        $payments = OrderPayment::with('order')
            ->when($method, fn ($q) => $q->where('provider', $method))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('reference_number', 'like', '%' . $keyword . '%')
                    ->orWhere('provider', 'like', '%' . $keyword . '%')
                    ->orWhere('status', 'like', '%' . $keyword . '%')
                    ->orWhereHas('order', function ($q) use ($keyword) {
                        $q->where('order_number', 'like', '%' . $keyword . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.payments.index')->with([
            'payments' => $payments,
            'keyword' => $keyword,
            'method' => $method,
            'status' => $status,
            // Cash still to be collected by couriers, and COD refunds still to send.
            'awaitingCollection' => (float) OrderPayment::where('provider', 'cod')->where('status', OrderPayment::STATUS_PENDING)->get()->sum(fn ($p) => (float) $p->amount),
            'refundsToSend' => (float) OrderPayment::where('provider', 'cod')->where('status', OrderPayment::STATUS_REFUND_PENDING)->get()->sum(fn ($p) => (float) $p->amount),
        ]);
    }
}
