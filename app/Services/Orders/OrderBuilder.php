<?php

namespace App\Services\Orders;

use App\Exceptions\OutOfStockException;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderBilling;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * Creates an order from an explicit list of items. Used by website checkout (items from the
 * bag) and by manual orders in the admin, so both lock and take stock per variant the same way.
 */
class OrderBuilder
{
    /**
     * @param  array  $data
     *   user_id              ?int    customer account, or null for a guest order
     *   lines                array   [['product_id', 'sku_id' => ?int, 'qty', 'price' => unit price, 'properties' => ?array], ...]
     *   shipping_method_id   int
     *   shipping_charge      float
     *   shipping             array   firstname, lastname, street, city, phone, country, region, zipcode
     *   billing              array   the same plus email
     *   coupon_id            ?int    coupon_amount float
     *   discount_amount      float   order discount (manual orders)
     *   tax                  float
     *   payment              array   provider, status, reference (?), fee (?), method (?)
     *   channel              string  website, facebook, instagram, tiktok, phone, walk_in, other
     *   created_by_admin_id  ?int
     *   created_at           ?\DateTimeInterface  order date (backdated manual orders)
     *   after                ?callable(Order)     runs inside the transaction (checkout empties the bag)
     */
    public function create(array $data): Order
    {
        $lines = array_values($data['lines'] ?? []);
        if (!$lines) {
            throw new \RuntimeException('Add at least one item.');
        }

        return DB::transaction(function () use ($data, $lines) {
            // Check and take the stock first, under a row lock, so two buyers can't get the last unit.
            $this->reserveStock($lines);

            $itemsTotal = 0.0;
            foreach ($lines as $line) {
                $itemsTotal += (float) $line['price'] * (int) $line['qty'];
            }
            $shipping = round((float) ($data['shipping_charge'] ?? 0), 2);
            $coupon = round((float) ($data['coupon_amount'] ?? 0), 2);
            $discount = round((float) ($data['discount_amount'] ?? 0), 2);
            $tax = (float) ($data['tax'] ?? 0);
            $fee = round((float) ($data['payment']['fee'] ?? 0), 2);
            $gross = $itemsTotal + $shipping - $coupon - $discount + $tax + $fee;

            $order = new Order();
            $order->forceFill([
                'order_number' => date('Y') . now()->timestamp,
                'user_id' => $data['user_id'] ?? null,
                'shipping_method_id' => $data['shipping_method_id'],
                'gross_total' => $gross,
                'net_total' => $itemsTotal,
                'shipping_charge' => $shipping,
                'status' => Order::STATUS_CONFIRMED,
                'num_items_sold' => count($lines),
                'tax_total' => $tax,
                'coupon_id' => $data['coupon_id'] ?? null,
                'coupon_amount' => $coupon,
                'discount_amount' => $discount,
                'payment_fee' => $fee,
                'channel' => $data['channel'] ?? 'website',
                'created_by_admin_id' => $data['created_by_admin_id'] ?? null,
            ]);
            if (!empty($data['created_at'])) {
                $order->created_at = $data['created_at'];
            }
            $order->save();

            OrderShipping::create(array_merge($this->address($data['shipping'] ?? []), ['order_id' => $order->id]));
            OrderBilling::create(array_merge($this->address($data['billing'] ?? $data['shipping'] ?? []), [
                'email' => $data['billing']['email'] ?? null,
                'order_id' => $order->id,
            ]));

            foreach ($lines as $line) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product_id'],
                    'sku_id' => $line['sku_id'] ?? null,
                    'qty' => (int) $line['qty'],
                    'price' => (float) $line['price'],
                    'properties' => $line['properties'] ?? null,
                ]);
            }

            $payment = $data['payment'];
            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $gross,
                'provider' => $payment['provider'],
                // Payments without a provider reference (cash on delivery, cash) use the order number.
                'reference_number' => ($payment['reference'] ?? null) ?: (strtoupper($payment['provider']) . '-' . $order->order_number),
                'status' => $payment['status'],
                'method' => $payment['method'] ?? null,
            ]);

            if (!empty($data['coupon_id'])) {
                Coupon::whereKey($data['coupon_id'])->increment('usage');
            }

            if (isset($data['after']) && is_callable($data['after'])) {
                ($data['after'])($order);
            }

            return $order;
        });
    }

    private function address(array $a): array
    {
        $fields = ['firstname', 'lastname', 'street', 'city', 'phone', 'country', 'region', 'zipcode'];

        return array_combine($fields, array_map(fn ($f) => $a[$f] ?? null, $fields));
    }

    /** Lock and check stock for every product and variant, then subtract it. */
    private function reserveStock(array $lines): void
    {
        $this->assertStock($lines, true);

        foreach ($lines as $line) {
            if (!empty($line['sku_id'])) {
                ProductSku::whereKey($line['sku_id'])->decrement('qty', (int) $line['qty']);
            }
            Stock::where('product_id', $line['product_id'])->decrement('qty', (int) $line['qty']);
        }
    }

    /** Throw OutOfStockException if any product or variant has fewer units than the lines need. */
    public function assertStock(array $lines, bool $lock = false): void
    {
        $needed = [];
        $neededSku = [];
        foreach ($lines as $line) {
            if (!empty($line['sku_id'])) {
                $neededSku[$line['sku_id']] = ($neededSku[$line['sku_id']] ?? 0) + (int) $line['qty'];
            } else {
                $needed[$line['product_id']] = ($needed[$line['product_id']] ?? 0) + (int) $line['qty'];
            }
        }

        if ($neededSku) {
            $query = ProductSku::with('product')->whereIn('id', array_keys($neededSku));
            $skus = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('id');
            foreach ($neededSku as $skuId => $qty) {
                $sku = $skus->get($skuId);
                if ((int) optional($sku)->qty < $qty) {
                    $name = $sku ? $sku->product->name . ' (' . $sku->label() . ')' : 'This item';
                    throw new OutOfStockException($name, (int) optional($sku)->qty);
                }
            }
        }

        if ($needed) {
            $query = Stock::whereIn('product_id', array_keys($needed));
            $stocks = ($lock ? $query->lockForUpdate() : $query)->get()->keyBy('product_id');
            foreach ($needed as $productId => $qty) {
                $available = (int) optional($stocks->get($productId))->qty;
                if ($available < $qty) {
                    throw new OutOfStockException(optional(Product::find($productId))->name ?? 'This item', $available);
                }
            }
        }
    }
}
