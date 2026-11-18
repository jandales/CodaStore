<?php

namespace App\Services\Orders;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use App\Services\OrderNotifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Orders created by staff for sales outside the website (Facebook, phone, walk-in…). */
class ManualOrders
{
    private $builder;

    public function __construct(OrderBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function create(array $data, Admin $admin): Order
    {
        $lines = $this->priceLines($data['lines']);
        $itemsTotal = array_sum(array_map(fn ($l) => $l['price'] * $l['qty'], $lines));
        $discount = $this->discount($data, $itemsTotal);
        $email = $data['email'] ?? null;

        return DB::transaction(function () use ($data, $admin, $lines, $discount, $email) {
            // Customer: existing account, a new account (with an email), or a guest order.
            $user = null;
            if ($data['customer_mode'] === 'existing') {
                $user = User::findOrFail($data['user_id']);
                $email = $email ?: $user->email;
            } elseif ($email) {
                $user = User::create([
                    'name' => trim($data['firstname'] . ' ' . $data['lastname']),
                    'email' => $email,
                ]);
                $user->forceFill(['contact' => $data['phone']])->save();
            }

            $address = array_intersect_key($data, array_flip(['firstname', 'lastname', 'street', 'city', 'phone', 'country', 'region', 'zipcode']));

            $payment = [
                'cod' => ['provider' => 'cod', 'status' => OrderPayment::STATUS_PENDING],
                'paid' => ['provider' => 'manual', 'status' => OrderPayment::STATUS_PAID, 'method' => $data['payment_method'] ?? null],
                'awaiting' => ['provider' => 'manual', 'status' => OrderPayment::STATUS_AWAITING, 'method' => $data['payment_method'] ?? null],
            ][$data['payment']];
            $payment['reference'] = $data['payment_reference'] ?? null;

            // Backdated orders keep the time of day they were entered.
            $date = Carbon::parse($data['order_date']);
            $createdAt = $date->isToday() ? now() : $date->setTimeFrom(now());

            $order = $this->builder->create([
                'user_id' => optional($user)->id,
                'lines' => $lines,
                'shipping_method_id' => (int) $data['shipping_method_id'],
                'shipping_charge' => (float) $data['shipping_charge'],
                'shipping' => $address,
                'billing' => $address + ['email' => $email],
                'discount_amount' => $discount,
                'payment' => $payment,
                'channel' => $data['channel'],
                'created_by_admin_id' => $admin->id,
                'created_at' => $createdAt,
            ]);

            if (!empty($data['note'])) {
                OrderNote::create(['order_id' => $order->id, 'admin_id' => $admin->id, 'body' => trim($data['note'])]);
            }

            ActivityLog::record('order.manual', $order, 'Created manual order (' . $order->channelLabel() . ', ₱' . number_format((float) $order->gross_total, 2) . ')');

            return $order;
        });
    }

    /** Send the confirmation email (after the order is saved). */
    public function notify(Order $order): void
    {
        app(OrderNotifier::class)->confirmed($order);
    }

    /**
     * Catalogue prices for each line (the variant's price, with any sale). A product with
     * variants needs one; a variant must belong to its product.
     */
    private function priceLines(array $input): array
    {
        $lines = [];
        foreach (array_values($input) as $i => $line) {
            $product = Product::with('skus')->find($line['product_id']);
            $sku = !empty($line['sku_id']) ? ProductSku::find($line['sku_id']) : null;

            if ($product->skus->isNotEmpty() && !$sku) {
                throw ValidationException::withMessages(["lines.$i.sku_id" => 'Choose a size / option for ' . $product->name . '.']);
            }
            if ($sku && (int) $sku->product_id !== (int) $product->id) {
                throw ValidationException::withMessages(["lines.$i.sku_id" => 'That option belongs to another product.']);
            }

            $lines[] = [
                'product_id' => $product->id,
                'sku_id' => optional($sku)->id,
                'qty' => (int) $line['qty'],
                'price' => $sku ? $sku->effectivePrice() : $product->effectivePrice(),
                'properties' => $sku ? $sku->options : null,
            ];
        }

        return $lines;
    }

    private function discount(array $data, float $itemsTotal): float
    {
        $value = (float) ($data['discount_value'] ?? 0);
        if ($value <= 0) {
            return 0.0;
        }
        $amount = ($data['discount_type'] ?? 'amount') === 'percent' ? $itemsTotal * min($value, 100) / 100 : $value;

        return round(min($amount, $itemsTotal), 2);
    }

    /** "Mark as paid" for a manual order that was awaiting payment. */
    public function markPaid(Order $order, string $method, ?string $reference): OrderPayment
    {
        $payment = $order->payment()->first();
        if (!$payment || $payment->provider !== 'manual' || $payment->status !== OrderPayment::STATUS_AWAITING) {
            throw ValidationException::withMessages(['payment_method' => 'This order is not waiting for payment.']);
        }

        $payment->update([
            'status' => OrderPayment::STATUS_PAID,
            'method' => $method,
            'reference_number' => $reference ?: $payment->reference_number,
            'collected_at' => now(),
        ]);
        ActivityLog::record('order.paid', $order, 'Marked as paid (' . $payment->methodLabel() . ')');

        return $payment;
    }
}
