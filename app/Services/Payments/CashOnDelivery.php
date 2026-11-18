<?php

namespace App\Services\Payments;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;

/**
 * Cash on delivery rules, set in Admin > Settings > Payment methods. Always checked on the
 * server when the order is placed; the payment page uses the same checks to explain why COD
 * isn't offered.
 */
class CashOnDelivery
{
    public const PROVIDER = 'cod';

    private function settings(): ?GeneralSetting
    {
        return GeneralSetting::find(1);
    }

    public function enabled(): bool
    {
        $settings = $this->settings();

        return $settings ? (bool) $settings->cod_enabled : false;
    }

    public function fee(): float
    {
        return round((float) optional($this->settings())->cod_fee, 2);
    }

    public function maxTotal(): ?float
    {
        $max = optional($this->settings())->cod_max_total;

        return $max === null || (float) $max <= 0 ? null : (float) $max;
    }

    /** Shipping method ids that allow COD, or null for all of them. */
    public function shippingMethodIds(): ?array
    {
        $ids = optional($this->settings())->cod_shipping_methods;

        return is_array($ids) && $ids ? array_map('intval', $ids) : null;
    }

    public function maxOpenOrders(): int
    {
        return (int) (optional($this->settings())->cod_max_open_orders ?? 2);
    }

    /** COD orders of this customer whose cash hasn't been collected yet. */
    public static function openOrders(User $user): int
    {
        return Order::where('user_id', $user->id)
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_SHIPPED])
            ->whereHas('payment', fn ($q) => $q->where('provider', self::PROVIDER)->where('status', OrderPayment::STATUS_PENDING))
            ->count();
    }

    /**
     * Why COD can't be used for this order, or null when it can.
     *
     * @param  float  $orderTotal  items + shipping - coupon, before any COD fee
     */
    public function unavailableReason(float $orderTotal, ?int $shippingMethodId, ?User $user): ?string
    {
        if (!$this->enabled()) {
            return 'Cash on delivery is not available right now.';
        }

        $max = $this->maxTotal();
        if ($max !== null && $orderTotal > $max) {
            return 'Cash on delivery is available for orders up to ₱' . number_format($max, 2) . '.';
        }

        $allowed = $this->shippingMethodIds();
        if ($allowed !== null && !in_array((int) $shippingMethodId, $allowed, true)) {
            return 'Cash on delivery is not available for the shipping method you chose.';
        }

        $limit = $this->maxOpenOrders();
        if ($user && $limit > 0 && self::openOrders($user) >= $limit) {
            return 'You already have ' . $limit . ' cash on delivery ' . ($limit === 1 ? 'order' : 'orders')
                . ' waiting to be paid. Please pay with PayPal, or wait until ' . ($limit === 1 ? 'it arrives' : 'they arrive') . '.';
        }

        return null;
    }
}
