<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

/**
 * PayPal Checkout (Orders v2) through srmklive/paypal. Credentials come from config/paypal.php
 * (PAYPAL_MODE, PAYPAL_SANDBOX_CLIENT_ID / _SECRET or the live equivalents).
 */
class PayPalGateway implements PaymentGateway
{
    private ?PayPalClient $client = null;

    public function name(): string
    {
        return 'paypal';
    }

    public function createPayment(float $amount, string $currency, string $returnUrl, string $cancelUrl, string $reference): array
    {
        $response = $this->call(fn () => $this->client()->createOrder([
            'intent' => 'CAPTURE',
            'application_context' => [
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING', // address is collected in our checkout
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
            'purchase_units' => [[
                'reference_id' => $reference,
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
        ]));

        $approve = collect($response['links'] ?? [])->firstWhere('rel', 'approve');
        if (empty($response['id']) || !$approve) {
            throw new PaymentException($this->errorMessage($response, 'PayPal could not start the payment.'));
        }

        return ['id' => $response['id'], 'approve_url' => $approve['href']];
    }

    public function capture(string $paymentId): array
    {
        $response = $this->call(fn () => $this->client()->capturePaymentOrder($paymentId));
        $capture = $response['purchase_units'][0]['payments']['captures'][0] ?? null;

        return [
            'completed' => ($response['status'] ?? null) === 'COMPLETED' && ($capture['status'] ?? null) === 'COMPLETED',
            'capture_id' => $capture['id'] ?? null,
            'amount' => isset($capture['amount']['value']) ? (float) $capture['amount']['value'] : null,
            'currency' => $capture['amount']['currency_code'] ?? null,
            'message' => ($response['status'] ?? null) === 'COMPLETED' ? null : $this->errorMessage($response, 'PayPal did not complete the payment.'),
        ];
    }

    public function refund(string $captureId, float $amount, string $currency, string $note): bool
    {
        try {
            $response = $this->call(fn () => $this->client()->setCurrency($currency)
                ->refundCapturedPayment($captureId, 'refund-' . $captureId, $amount, $note));
        } catch (PaymentException $e) {
            logger()->error('PayPal refund failed; refund manually in PayPal', ['capture_id' => $captureId, 'amount' => $amount]);
            return false;
        }

        $ok = in_array($response['status'] ?? null, ['COMPLETED', 'PENDING'], true);
        if (!$ok) {
            logger()->error('PayPal refund not accepted; refund manually in PayPal', ['capture_id' => $captureId, 'response' => $response]);
        }

        return $ok;
    }

    /** True when credentials for the configured mode are present. */
    public static function isConfigured(): bool
    {
        $mode = config('paypal.mode') === 'live' ? 'live' : 'sandbox';

        return filled(config("paypal.$mode.client_id")) && filled(config("paypal.$mode.client_secret"));
    }

    /** Run a PayPal API call, turning library/network errors into a PaymentException. */
    private function call(callable $request)
    {
        try {
            return $request();
        } catch (PaymentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new PaymentException('We could not reach PayPal. Please try again in a moment.');
        }
    }

    private function client(): PayPalClient
    {
        if ($this->client) {
            return $this->client;
        }

        if (!self::isConfigured()) {
            logger()->error('PayPal credentials are missing (PAYPAL_MODE / PAYPAL_*_CLIENT_ID / _SECRET).');
            throw new PaymentException('Online payment is not available right now. Please try again later.');
        }

        $client = new PayPalClient;
        $client->setApiCredentials(config('paypal'));
        $client->setCurrency(config('paypal.currency', 'PHP'));
        $token = $client->getAccessToken();
        if (empty($token['access_token'])) {
            throw new PaymentException('PayPal is not configured correctly. Please try again later.');
        }

        return $this->client = $client;
    }

    private function errorMessage($response, string $fallback): string
    {
        // Log the provider detail; show the shopper something plain.
        logger()->warning('PayPal API error', ['response' => $response]);

        return $fallback;
    }
}
