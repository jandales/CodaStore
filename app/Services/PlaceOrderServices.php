<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Stock;
use App\Models\ProductSku;
use App\Models\Coupon;
use App\Models\OrderBilling;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Models\OrderShipping;
use App\Models\PaymentDetail;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;

class PlaceOrderServices {

    private $order;
    private $status = 'confirmed';
    private $payment = [];

    private function orderNumber()
    {
        return date('Y')."".now()->timestamp;
    }

    /**
     * The amount to charge for the current bag, after re-checking it. Used before sending the
     * shopper to PayPal; storeOrder() recomputes the same figure when the payment comes back.
     *
     * @return array{total: float, shipping_charge: float, tax: float}
     */
    public function quote(float $paymentFee = 0): array
    {
        $cart = Self::currentCart();
        Self::assertStock($cart->items);
        $totals = Self::totals($cart, $paymentFee);

        return [
            'total' => round($totals['total'], 2),
            'shipping_charge' => (float) $totals['shipping_charge'],
            'shipping_method_id' => $totals['shipping_method']->id,
            'tax' => (float) $totals['tax'],
            'payment_fee' => (float) $totals['payment_fee'],
        ];
    }

    private function currentCart(): Cart
    {
        $cart = Cart::ByUser()->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw new \RuntimeException('Your bag is empty.');
        }

        // Always charge from current prices, not a total saved when items were added.
        return $cart->recalculateTotal();
    }

    /**
     * Bag total + shipping - coupon + tax, plus any payment-method fee (cash on delivery).
     * Shipping comes from the chosen shipping method, which is also what is stored on the order.
     */
    private function totals(Cart $cart, float $paymentFee = 0): array
    {
        $shipping_method = ShippingMethod::find(optional((object) session()->get('shipping_method'))->id);
        if (!$shipping_method) {
            throw new \RuntimeException('Please choose a shipping method.');
        }

        $shipping_charge = (float) $shipping_method->amount;
        $subtotal = (float) $cart->total + $shipping_charge - (float) $cart->discount;
        $tax = tax($subtotal);

        return [
            'shipping_method' => $shipping_method,
            'shipping_charge' => $shipping_charge,
            'tax' => $tax,
            'payment_fee' => round($paymentFee, 2),
            'total' => $subtotal + $tax + round($paymentFee, 2),
        ];
    }

    /**
     * Create the order from the current bag and checkout session.
     *
     * Call this after the payment provider has confirmed the payment, or for cash on delivery.
     *
     * @param  array  $payment  ['provider' => 'paypal', 'reference' => capture id, 'status' => 'paid']
     *                          or ['provider' => 'cod', 'reference' => null, 'status' => 'pending', 'fee' => 50.0]
     */
    public function storeOrder(Request $request, array $payment)
    {
        $this->payment = $payment;

        $cart = Self::currentCart();
        ['shipping_method' => $shipping_method, 'shipping_charge' => $shipping_charge, 'tax' => $tax, 'total' => $gross_total, 'payment_fee' => $payment_fee] = Self::totals($cart, (float) ($payment['fee'] ?? 0));

        return DB::transaction(function () use ($cart,$shipping_method, $shipping_charge, $gross_total,$tax,$request,$payment_fee){

            // Check and take the stock first, under a row lock, so two shoppers can't buy the last unit.
            Self::reserveStock($cart->items);

            $this->order = Order::create([
                'order_number' => Self::orderNumber(),
                'user_id' => auth()->user()->id,
                'shipping_method_id' => $shipping_method->id,
                'gross_total' => $gross_total,
                'net_total' => $cart->total,
                'shipping_charge' => $shipping_charge,
                'status' => $this->status,
                'num_items_sold' => $cart->items->count(),
                'tax_total' => $tax,
                'coupon_id' => $cart->coupon_id,
                'coupon_amount' => $cart->discount,
                'payment_fee' => $payment_fee,
            ]);


            Self::storeOrderShipping();

            Self::storeOrderBilling();

            Self::storeOrderItem($cart->items);

            Self::storeOrderPayment($gross_total);

            Self::updateCouponUsage($cart->coupon_id);

            Self::deleteCheckoutSession();

            $cart->delete();

            return $this->order;
        });



    }

    private function storeOrderBilling()
    {
        return OrderBilling::create([
            'firstname' => request()->input('is_new_billing') == 1 ?  request()->input('billing_firstname') : session()->get('shipping_address')['firstname'],
            'lastname' => request()->input('is_new_billing') == 1 ?  request()->input('billing_lastname') : session()->get('shipping_address')['lastname'],
            'email' =>  session()->get('email'),
            'street' => request()->input('is_new_billing') == 1 ? request()->input('billing_street') : session()->get('shipping_address')['street'],
            'city' => request()->input('is_new_billing') == 1 ?  request()->input('billing_city') : session()->get('shipping_address')['city'],
            'phone' =>  request()->input('is_new_billing') == 1 ? request()->input('billing_phone') :session()->get('shipping_address')['phone'],
            'country' =>request()->input('is_new_billing') == 1 ? request()->input('billing_country') : session()->get('shipping_address')['country'],
            'region' => request()->input('is_new_billing') == 1 ?  request()->input('billing_region') : session()->get('shipping_address')['region'],
            'zipcode' => request()->input('is_new_billing') == 1 ? request()->input('billing_zipcode') :session()->get('shipping_address')['zipcode'],
            'order_id' =>  $this->order->id,
        ]);
    }


    private function storeOrderShipping()
    {
        OrderShipping::create([
            'firstname' => session()->get('shipping_address')['firstname'],
            'lastname' => session()->get('shipping_address')['lastname'],
            'street' => session()->get('shipping_address')['street'],
            'city' => session()->get('shipping_address')['city'],
            'phone' =>session()->get('shipping_address')['phone'],
            'country' => session()->get('shipping_address')['country'],
            'region' => session()->get('shipping_address')['region'],
            'zipcode' => session()->get('shipping_address')['zipcode'],
            'order_id' => $this->order->id,
        ]);
    }


    private function storeOrderPayment($amount)
    {
        return OrderPayment::create([
            'order_id' => $this->order->id,
            'amount' => $amount,
            'provider' => $this->payment['provider'],
            // Cash on delivery has no provider reference; use the order number.
            'reference_number' => $this->payment['reference'] ?? ('COD-' . $this->order->order_number),
            'status' => $this->payment['status'],
        ]);
    }


    private function storeOrderItem($items)
    {
        foreach($items as $item)
        {
            OrderProduct::create([
                'order_id' => $this->order->id,
                'product_id' => $item->product_id,
                'sku_id' => $item->sku_id,
                'qty' => $item->qty,
                'price' => $item->unitPrice(),
                'properties' => $item->attributes,
            ]);

            $item->delete();
        }
    }

    /**
     * Lock the stock rows for every product and variant in the bag, make sure there is enough
     * of each, then subtract. Throws OutOfStockException (rolling back the whole order) if not.
     */
    private function reserveStock($items)
    {
        Self::assertStock($items, true);

        foreach ($items as $item) {
            if ($item->sku_id) {
                ProductSku::whereKey($item->sku_id)->decrement('qty', (int) $item->qty);
            }
            Stock::where('product_id', $item->product_id)->decrement('qty', (int) $item->qty);
        }
    }

    /** Throw OutOfStockException if any product or variant in the bag has fewer units than requested. */
    private function assertStock($items, bool $lock = false)
    {
        $needed = [];      // product id => qty (products without variants)
        $neededSku = [];   // variant id => qty
        foreach ($items as $item) {
            if ($item->sku_id) {
                $neededSku[$item->sku_id] = ($neededSku[$item->sku_id] ?? 0) + (int) $item->qty;
            } else {
                $needed[$item->product_id] = ($needed[$item->product_id] ?? 0) + (int) $item->qty;
            }
        }

        if ($neededSku) {
            $query = ProductSku::whereIn('id', array_keys($neededSku));
            $skus = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
            foreach ($neededSku as $skuId => $qty) {
                $sku = $skus->get($skuId);
                $available = (int) optional($sku)->qty;
                if ($available < $qty) {
                    $line = $items->firstWhere('sku_id', $skuId);
                    $name = optional($line->product)->name ?? 'This item';
                    throw new OutOfStockException($sku ? $name . ' (' . $sku->label() . ')' : $name, $available);
                }
            }
        }

        if ($needed) {
            $query = Stock::whereIn('product_id', array_keys($needed));
            $stocks = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('product_id');

            foreach ($needed as $productId => $qty) {
                $available = (int) optional($stocks->get($productId))->qty;
                if ($available < $qty) {
                    $name = optional($items->firstWhere('product_id', $productId)->product)->name ?? 'This item';
                    throw new OutOfStockException($name, $available);
                }
            }
        }
    }

    private function updateCouponUsage($coupon_id)
    {
        $coupon = Coupon::find($coupon_id);
        if(empty($coupon)) return;
        $coupon->usage += 1;
        $coupon->save();
    }

    private function deleteCheckoutSession()
    {
        session()->forget('email');
        session()->forget('shipping_address');
        session()->forget('shipping_method');
        session()->forget('shipping_charge');

    }
}