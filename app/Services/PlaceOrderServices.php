<?php

namespace App\Services;

use App\Services\Orders\OrderBuilder;

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
        app(OrderBuilder::class)->assertStock(Self::linesFromCart($cart));
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
        $cart = Self::currentCart();
        ['shipping_method' => $shipping_method, 'shipping_charge' => $shipping_charge, 'tax' => $tax] = Self::totals($cart, (float) ($payment['fee'] ?? 0));

        $shipping = (array) session()->get('shipping_address');
        $billing = $request->input('is_new_billing') == 1
            ? [
                'firstname' => $request->input('billing_firstname'), 'lastname' => $request->input('billing_lastname'),
                'street' => $request->input('billing_street'), 'city' => $request->input('billing_city'),
                'phone' => $request->input('billing_phone'), 'country' => $request->input('billing_country'),
                'region' => $request->input('billing_region'), 'zipcode' => $request->input('billing_zipcode'),
            ]
            : $shipping;
        $billing['email'] = session()->get('email');

        return app(OrderBuilder::class)->create([
            'user_id' => auth()->id(),
            'lines' => Self::linesFromCart($cart),
            'shipping_method_id' => $shipping_method->id,
            'shipping_charge' => $shipping_charge,
            'shipping' => $shipping,
            'billing' => $billing,
            'coupon_id' => $cart->coupon_id,
            'coupon_amount' => (float) $cart->discount,
            'tax' => $tax,
            'payment' => $payment,
            'channel' => 'website',
            // Inside the same transaction: the bag and checkout session are used up.
            'after' => function () use ($cart) {
                foreach ($cart->items as $item) {
                    $item->delete();
                }
                Self::deleteCheckoutSession();
                $cart->delete();
            },
        ]);
    }

    /** The bag as order lines, priced from the catalogue now (per variant). */
    private function linesFromCart(Cart $cart): array
    {
        return $cart->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'sku_id' => $item->sku_id,
            'qty' => (int) $item->qty,
            'price' => $item->unitPrice(),
            'properties' => $item->attributes,
        ])->all();
    }

    private function deleteCheckoutSession()
    {
        session()->forget('email');
        session()->forget('shipping_address');
        session()->forget('shipping_method');
        session()->forget('shipping_charge');

    }
}