<?php

namespace App\Contracts;

/**
 * A hosted payment provider (PayPal today). Card details never reach this application:
 * the shopper approves the payment on the provider's page and we capture it on return.
 */
interface PaymentGateway
{
    /** Provider name stored on order_payments.provider, e.g. "paypal". */
    public function name(): string;

    /**
     * Start a payment for the exact amount the server calculated.
     *
     * @return array{id: string, approve_url: string}
     */
    public function createPayment(float $amount, string $currency, string $returnUrl, string $cancelUrl, string $reference): array;

    /**
     * Capture an approved payment.
     *
     * @return array{completed: bool, capture_id: ?string, amount: ?float, currency: ?string, message: ?string}
     */
    public function capture(string $paymentId): array;

    /** Refund a captured payment in full. Returns true when the provider accepted the refund. */
    public function refund(string $captureId, float $amount, string $currency, string $note): bool;
}
