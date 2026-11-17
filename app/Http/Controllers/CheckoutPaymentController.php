<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Exceptions\OutOfStockException;
use App\Http\Requests\BillingRequest;
use App\Services\Payments\PaymentException;
use App\Services\PlaceOrderServices;
use Illuminate\Http\Request;

/**
 * Checkout payment through a hosted gateway (PayPal):
 *  1. start:  re-check bag + stock, create a payment for the server-calculated total, redirect to PayPal
 *  2. return: capture; only a COMPLETED capture creates the order (refunded if stock ran out meanwhile)
 *  3. cancel: back to the payment step with the bag untouched
 */
class CheckoutPaymentController extends Controller
{
    private const SESSION_KEY = 'checkout_payment';

    private $gateway;
    private $orders;

    public function __construct(PaymentGateway $gateway, PlaceOrderServices $orders)
    {
        $this->gateway = $gateway;
        $this->orders = $orders;
    }

    public function start(BillingRequest $request)
    {
        try {
            $quote = $this->orders->quote();
        } catch (OutOfStockException | \RuntimeException $e) {
            return redirect()->route('cart')->with('error', $e->getMessage());
        }

        $currency = config('paypal.currency', 'PHP');
        $reference = 'CODA-' . auth()->id() . '-' . now()->timestamp;

        try {
            $payment = $this->gateway->createPayment(
                $quote['total'],
                $currency,
                route('checkout.paypal.return'),
                route('checkout.paypal.cancel'),
                $reference
            );
        } catch (PaymentException $e) {
            return redirect()->route('checkout.payment')->with('error', $e->getMessage());
        }

        session([self::SESSION_KEY => [
            'payment_id' => $payment['id'],
            'amount' => $quote['total'],
            'currency' => $currency,
            'billing' => $request->only([
                'is_new_billing', 'billing_firstname', 'billing_lastname', 'billing_street', 'billing_city',
                'billing_phone', 'billing_country', 'billing_region', 'billing_zipcode',
            ]),
        ]]);

        return redirect()->away($payment['approve_url']);
    }

    public function return(Request $request)
    {
        $token = (string) $request->query('token');

        // Refreshing the return page after success must not create a second order.
        $done = session('checkout_completed_payments', []);
        if ($token !== '' && isset($done[$token])) {
            return redirect()->route('checkout.completed')->with('order_number', $done[$token]);
        }

        $pending = session(self::SESSION_KEY);
        if (!$pending || $token === '' || $token !== $pending['payment_id']) {
            return redirect()->route('checkout.payment')->with('error', "We couldn't match this payment to your checkout. You have not been charged twice; please try again.");
        }

        try {
            $capture = $this->gateway->capture($token);
        } catch (PaymentException $e) {
            return redirect()->route('checkout.payment')->with('error', $e->getMessage());
        }

        if (!$capture['completed'] || !$capture['capture_id']) {
            session()->forget(self::SESSION_KEY);
            return redirect()->route('checkout.payment')->with('error', $capture['message'] ?? 'PayPal did not complete the payment. You have not been charged.');
        }

        if ($capture['amount'] !== null && abs($capture['amount'] - $pending['amount']) > 0.009) {
            $refunded = $this->gateway->refund($capture['capture_id'], $capture['amount'], $pending['currency'], 'Amount mismatch, refunded automatically.');
            session()->forget(self::SESSION_KEY);
            return redirect()->route('cart')->with('error', 'Your bag changed while you were paying. ' . $this->refundSentence($refunded) . ' Please check your bag and try again.');
        }

        // Billing details chosen on the payment step.
        $request->merge($pending['billing'] ?? []);

        try {
            $order = $this->orders->storeOrder($request, [
                'provider' => $this->gateway->name(),
                'reference' => $capture['capture_id'],
                'status' => 'paid',
            ]);
        } catch (OutOfStockException | \RuntimeException $e) {
            $refunded = $this->gateway->refund($capture['capture_id'], $pending['amount'], $pending['currency'], 'Item sold out during checkout, refunded automatically.');
            session()->forget(self::SESSION_KEY);
            return redirect()->route('cart')->with('error', $e->getMessage() . ' ' . $this->refundSentence($refunded));
        }

        session()->forget(self::SESSION_KEY);
        session(['checkout_completed_payments' => $done + [$token => $order->order_number]]);

        app(\App\Services\OrderNotifier::class)->confirmed($order);

        return redirect()->route('checkout.completed')->with('order_number', $order->order_number);
    }

    private function refundSentence(bool $refunded): string
    {
        return $refunded
            ? 'Your payment has been refunded to PayPal.'
            : "We couldn't refund your payment automatically; our team has been notified and will refund you within 2 business days.";
    }

    public function cancel()
    {
        session()->forget(self::SESSION_KEY);

        return redirect()->route('checkout.payment')->with('message', 'Payment cancelled. Your bag is still here.');
    }
}
