<?php

namespace Tests\Fakes;

use App\Contracts\PaymentGateway;
use App\Services\Payments\PaymentException;

/** In-memory stand-in for PayPal used by the checkout tests. */
class FakePaymentGateway implements PaymentGateway
{
    public array $created = [];
    public array $captured = [];
    public array $refunds = [];

    public bool $failCreate = false;
    public bool $captureCompletes = true;
    public ?float $captureAmountOverride = null;
    public bool $refundSucceeds = true;

    public function name(): string
    {
        return 'paypal';
    }

    public function createPayment(float $amount, string $currency, string $returnUrl, string $cancelUrl, string $reference): array
    {
        if ($this->failCreate) {
            throw new PaymentException('PayPal could not start the payment.');
        }

        $id = 'PAY-' . (count($this->created) + 1);
        $this->created[$id] = compact('amount', 'currency', 'returnUrl', 'cancelUrl', 'reference');

        return ['id' => $id, 'approve_url' => 'https://paypal.test/approve/' . $id];
    }

    public function capture(string $paymentId): array
    {
        $this->captured[] = $paymentId;
        $amount = $this->captureAmountOverride ?? ($this->created[$paymentId]['amount'] ?? null);

        return [
            'completed' => $this->captureCompletes,
            'capture_id' => $this->captureCompletes ? 'CAP-' . $paymentId : null,
            'amount' => $amount,
            'currency' => 'PHP',
            'message' => $this->captureCompletes ? null : 'PayPal did not complete the payment.',
        ];
    }

    public function refund(string $captureId, float $amount, string $currency, string $note): bool
    {
        $this->refunds[] = compact('captureId', 'amount', 'currency', 'note');

        return $this->refundSucceeds;
    }
}
