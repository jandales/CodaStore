<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManualOrderRequest;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\Orders\ManualOrders;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin > Orders > Create order: orders taken outside the website. */
class ManualOrderController extends Controller
{
    public function create()
    {
        return view('admin.orders.create', [
            'shippingMethods' => ShippingMethod::orderBy('name')->get(['id', 'name', 'amount']),
            'channels' => collect(Order::CHANNELS)->except('website'),
            'paymentMethods' => OrderPayment::MANUAL_METHODS,
            'maxBackdate' => ManualOrderRequest::MAX_BACKDATE_DAYS,
        ]);
    }

    /** Product search for the item picker: name, product SKU or variant SKU. */
    public function products(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 1) {
            return response()->json(['products' => []]);
        }
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $products = Product::with(['stock', 'skus'])
            ->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('skus', fn ($s) => $s->where('sku', 'like', $like));
            })
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json(['products' => $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'image' => $p->imagePath,
            'published' => (int) $p->status === 1,
            'price' => $p->effectivePrice(),
            'stock' => $p->stockQty(),
            'variants' => $p->skus->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->label(),
                'price' => $s->effectivePrice(),
                'qty' => max(0, (int) $s->qty),
                'image' => $s->image,
            ])->values(),
        ])->values()]);
    }

    /** Customer search: name, email or phone, with their saved address. */
    public function customers(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json(['customers' => []]);
        }
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $users = User::with('shippingAddress')
            ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('contact', 'like', $like))
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(['customers' => $users->map(function ($u) {
            $address = $u->shippingAddress->sortByDesc('status')->first();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->contact,
                'address' => $address ? $address->only(['firstname', 'lastname', 'street', 'city', 'phone', 'country', 'region', 'zipcode']) : null,
            ];
        })->values()]);
    }

    public function store(ManualOrderRequest $request, ManualOrders $orders)
    {
        try {
            $order = $orders->create($request->validated(), auth('admin')->user());
        } catch (OutOfStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($request->boolean('send_email')) {
            $orders->notify($order);
        }

        return redirect()->route('admin.orders.show', [$order->encryptedId()])
            ->with('success', 'Order ' . $order->order_number . ' created.');
    }

    public function markPaid(Request $request, Order $order, ManualOrders $orders)
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(OrderPayment::MANUAL_METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ], ['payment_method.required' => 'Choose how the customer paid.']);

        $orders->markPaid($order, $data['payment_method'], $data['payment_reference'] ?? null);

        return back()->with('success', 'Marked as paid.');
    }
}
