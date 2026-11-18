<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/** CSV downloads of orders, customers and products for spreadsheets and accounting. */
class AdminExportController extends Controller
{
    public function orders(Request $request)
    {
        $status = $request->query('status');
        $orders = Order::with('user', 'billing', 'payment', 'items', 'createdBy')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('id');

        $rows = [['Order number', 'Date', 'Status', 'Customer', 'Email', 'Items', 'Subtotal', 'Shipping', 'Coupon', 'COD fee', 'Total', 'Payment method', 'Payment', 'Payment reference', 'Courier', 'Tracking number', 'Channel', 'Discount', 'Created by']];
        foreach ($orders->cursor() as $order) {
            $rows[] = [
                $order->order_number,
                $order->created_at->format('Y-m-d H:i'),
                $order->status,
                optional($order->billing)->personName() ?: optional($order->user)->name,
                optional($order->billing)->email ?: optional($order->user)->email,
                $order->totalItems(),
                self::amount($order->subtotal()),
                self::amount($order->shipping_charge),
                self::amount($order->coupon_amount),
                self::amount($order->payment_fee),
                self::amount($order->gross_total),
                $order->payment ? $order->payment->methodLabel() : '',
                optional($order->payment)->status,
                optional($order->payment)->reference_number,
                $order->courierName(),
                $order->tracking_number,
                $order->channelLabel(),
                self::amount($order->discount_amount),
                $order->createdBy ? (trim($order->createdBy->firstname . ' ' . $order->createdBy->lastname) ?: $order->createdBy->username) : '',
            ];
        }

        return self::download('orders' . ($status ? "-{$status}" : ''), $rows);
    }

    public function customers()
    {
        $customers = User::withCount(['orders' => fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])])
            ->withSum(['orders' => fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])], 'gross_total')
            ->orderBy('id');

        $rows = [['Name', 'Email', 'Phone', 'Signed up', 'Orders', 'Total spent']];
        foreach ($customers->cursor() as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                $user->contact,
                optional($user->created_at)->format('Y-m-d'),
                $user->orders_count,
                self::amount($user->orders_sum_gross_total ?? 0),
            ];
        }

        return self::download('customers', $rows);
    }

    public function products()
    {
        $products = Product::with('category', 'stock', 'skus')->orderBy('id');

        $rows = [['Name', 'SKU', 'Category', 'Status', 'Regular price', 'Sale price', 'On sale', 'Price now', 'Stock', 'Variant stock', 'Variant prices']];
        foreach ($products->cursor() as $product) {
            $rows[] = [
                $product->name,
                $product->sku,
                optional($product->category)->name,
                (int) $product->status === 1 ? 'Published' : 'Draft',
                self::amount($product->regular_price),
                $product->isOnSale() ? self::amount($product->sale_price) : '',
                $product->isOnSale() ? 'Yes' : 'No',
                self::amount($product->effectivePrice()),
                $product->stockQty(),
                // "S:5|M:3|XL:0" and "XL:700" (variants with their own price only).
                $product->skus->map(fn ($sku) => $sku->label() . ':' . (int) $sku->qty)->implode('|'),
                $product->skus->filter(fn ($sku) => $sku->price !== null)->map(fn ($sku) => $sku->label() . ':' . self::amount($sku->price))->implode('|'),
            ];
        }

        return self::download('products', $rows);
    }

    public function subscribers()
    {
        $rows = [['Email', 'Customer', 'Subscribed']];
        foreach (\App\Models\NewsletterSubscriber::active()->orderBy('id')->cursor() as $subscriber) {
            $rows[] = [
                $subscriber->email,
                $subscriber->user_id ? 'Yes' : 'No',
                optional($subscriber->created_at)->format('Y-m-d'),
            ];
        }

        return self::download('newsletter-subscribers', $rows);
    }

    private static function amount($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * A cell starting with = + - @ (or a tab/CR) is run as a formula by Excel and Sheets,
     * so prefix it with an apostrophe. Plain negative numbers are left alone.
     */
    public static function safeCell($value): string
    {
        $value = (string) $value;
        if ($value !== '' && strpbrk($value[0], "=+-@\t\r") !== false && !is_numeric($value)) {
            return "'" . $value;
        }

        return $value;
    }

    private static function download(string $name, array $rows)
    {
        return self::csv("coda-{$name}-" . now()->format('Y-m-d') . '.csv', $rows);
    }

    /** A CSV download with formula-safe cells and a BOM for Excel. */
    public static function csv(string $filename, array $rows)
    {
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads ₱ and accents as UTF-8
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'safeCell'], $row));
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
