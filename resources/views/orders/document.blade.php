@php
    $isInvoice = $type === 'invoice';
    $title = $isInvoice ? 'Invoice' : 'Packing slip';
    $settings = siteSettings();
    $company = $settings ? array_filter([
        $settings->campany_address,
        trim(implode(', ', array_filter([$settings->campany_city, $settings->campany_region])) . ' ' . $settings->campany_zipcode),
        $settings->campany_country,
    ]) : [];
    $shipping = $order->shipping;
    $billing = $order->billing;
    $payment = $order->payment;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} {{ $order->order_number }} | Coda</title>
    <meta name="robots" content="noindex">
    <style>
        :root { --ink: #1c1b19; --muted: #6b675f; --line: #e4e0d8; --paper: #fff; --bg: #f5f3ef; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); font: 14px/1.5 -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        .toolbar { max-width: 820px; margin: 24px auto 0; padding: 0 16px; display: flex; gap: 8px; justify-content: flex-end; }
        .toolbar button, .toolbar a { font: inherit; padding: 8px 16px; border-radius: 6px; border: 1px solid var(--ink); background: var(--ink); color: #fff; cursor: pointer; text-decoration: none; }
        .toolbar a { background: transparent; color: var(--ink); }
        .doc { max-width: 820px; margin: 16px auto 40px; background: var(--paper); padding: 48px; border: 1px solid var(--line); }
        header { display: flex; justify-content: space-between; gap: 24px; align-items: flex-start; border-bottom: 2px solid var(--ink); padding-bottom: 20px; }
        .brand { font-family: Georgia, "Times New Roman", serif; font-size: 28px; letter-spacing: .3em; margin: 0; }
        .company { color: var(--muted); margin: 6px 0 0; font-size: 13px; }
        h1 { margin: 0; font-size: 22px; font-weight: 600; text-align: right; }
        .meta { margin: 6px 0 0; text-align: right; color: var(--muted); font-size: 13px; }
        .meta strong { color: var(--ink); font-weight: 600; }
        .stamp-wrap { text-align: right; }
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 28px 0; }
        .parties h2 { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); margin: 0 0 6px; font-weight: 600; }
        .parties p { margin: 0; }
        .parties p + p { margin-top: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); font-weight: 600; border-bottom: 1px solid var(--ink); padding: 8px 6px; }
        td { border-bottom: 1px solid var(--line); padding: 10px 6px; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .name { text-transform: capitalize; font-weight: 500; }
        .variant { color: var(--muted); font-size: 12px; }
        .check { width: 18px; height: 18px; border: 1.5px solid var(--ink); display: inline-block; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        .totals { margin-left: auto; width: 300px; margin-top: 16px; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 6px; }
        .totals .grand { border-top: 2px solid var(--ink); margin-top: 6px; padding-top: 10px; font-weight: 700; font-size: 16px; }
        .totals .vat { color: var(--muted); font-size: 12px; }
        .note { margin-top: 32px; color: var(--muted); font-size: 12px; border-top: 1px solid var(--line); padding-top: 16px; }
        .stamp { display: inline-block; margin-top: 8px; padding: 2px 10px; border: 1.5px solid currentColor; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .stamp--paid { color: #1d7a4c; }
        .stamp--refunded { color: #b3261e; }
        .stamp--cod { color: #8a5a00; }
        @media (max-width: 640px) {
            .doc { padding: 24px 16px; margin: 12px 0 24px; border-left: 0; border-right: 0; }
            header { flex-direction: column; }
            .parties { grid-template-columns: 1fr; }
            h1, .meta, .stamp-wrap { text-align: left; }
            .totals { width: 100%; }
        }
        @media print {
            @page { margin: 14mm; }
            body { background: #fff; }
            .toolbar { display: none; }
            .doc { border: 0; margin: 0; padding: 0; max-width: none; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ $backUrl }}">Back</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>
    <main class="doc">
        <header>
            <div>
                <p class="brand">CODA</p>
                @if ($settings && ($settings->campany_name || $company))
                    <p class="company">
                        @if ($settings->campany_name){{ $settings->campany_name }}<br>@endif
                        @foreach ($company as $line){{ $line }}<br>@endforeach
                        @if ($settings->campany_phone){{ $settings->campany_phone }}<br>@endif
                        @if ($settings->site_email){{ $settings->site_email }}@endif
                    </p>
                @endif
            </div>
            <div>
                <h1>{{ $title }}</h1>
                <p class="meta">
                    Order <strong>{{ $order->order_number }}</strong><br>
                    {{ $order->created_at->format('F j, Y') }}
                    @if (!$isInvoice && $order->shippingMethod)<br>{{ $order->shippingMethod->name }}@endif
                </p>
                @if ($isInvoice && $payment && in_array($payment->status, ['paid', 'refunded']))
                    <div class="stamp-wrap">
                        <span class="stamp stamp--{{ $payment->status }}">{{ $payment->status === 'paid' ? 'Paid' : 'Refunded' }}</span>
                    </div>
                @elseif ($payment && $payment->isCashOnDelivery() && $payment->status === 'pending')
                    <div class="stamp-wrap">
                        <span class="stamp stamp--cod">Cash on delivery · @money($payment->amount) due</span>
                    </div>
                @endif
            </div>
        </header>

        <section class="parties">
            <div>
                <h2>Ship to</h2>
                @if ($shipping)
                    <p>{{ $shipping->personName() }}<br>{{ $shipping->street }}<br>{{ trim($shipping->city . ', ' . $shipping->region . ' ' . $shipping->zipcode, ', ') }}<br>{{ $shipping->country }}<br>{{ $shipping->phone }}</p>
                @endif
            </div>
            @if ($isInvoice)
                <div>
                    <h2>Bill to</h2>
                    @if ($billing)
                        <p>{{ $billing->personName() }}<br>{{ $billing->email }}<br>{{ $billing->phone }}</p>
                    @endif
                    @if ($payment && $payment->isCashOnDelivery())
                        <p>Cash on delivery · {{ $payment->statusLabel() }}</p>
                    @elseif ($payment)
                        <p>Paid with {{ $payment->methodLabel() }}@if ($payment->reference_number) · {{ $payment->reference_number }}@endif</p>
                    @endif
                </div>
            @else
                <div>
                    <h2>Contents</h2>
                    <p>{{ $order->totalItems() }} {{ \Illuminate\Support\Str::plural('item', $order->totalItems()) }}</p>
                    @if ($payment && $payment->isCashOnDelivery() && $payment->status === 'pending')
                        <p><strong>Cash on delivery: collect @money($payment->amount)</strong></p>
                    @endif
                </div>
            @endif
        </section>

        <table>
            <thead>
                <tr>
                    @if (!$isInvoice)<th scope="col" style="width:32px"><span class="sr-only">Packed</span></th>@endif
                    <th scope="col">Item</th>
                    @if (!$isInvoice)<th scope="col">SKU</th>@endif
                    <th scope="col" class="num">Qty</th>
                    @if ($isInvoice)
                        <th scope="col" class="num">Unit price</th>
                        <th scope="col" class="num">Amount</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        @if (!$isInvoice)<td><span class="check" aria-hidden="true"></span></td>@endif
                        <td>
                            <div class="name">{{ optional($item->product)->name ?? 'Removed product' }}</div>
                            @if ($item->properties)
                                <div class="variant">{{ collect($item->properties)->map(fn ($value, $key) => ucfirst($key) . ': ' . $value)->implode(' · ') }}</div>
                            @endif
                        </td>
                        @if (!$isInvoice)<td>{{ optional($item->product)->sku ?: '—' }}</td>@endif
                        <td class="num">{{ $item->qty }}</td>
                        @if ($isInvoice)
                            <td class="num">@money($item->price)</td>
                            <td class="num">@money($item->total())</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($isInvoice)
            <div class="totals">
                <div><span>Subtotal</span><span>@money($order->items->sum(fn ($i) => $i->total()))</span></div>
                <div><span>Shipping</span><span>@money($order->shipping_charge)</span></div>
                @if ((float) $order->coupon_amount > 0)
                    <div><span>Coupon</span><span>−@money($order->coupon_amount)</span></div>
                @endif
                @if ((float) $order->payment_fee > 0)
                    <div><span>COD fee</span><span>@money($order->payment_fee)</span></div>
                @endif
                <div class="grand"><span>Total</span><span>@money($order->gross_total)</span></div>
                <div class="vat"><span>Prices include VAT</span></div>
            </div>
            <p class="note">Thank you for shopping with Coda. Keep this invoice for returns and exchanges.</p>
        @else
            <p class="note">Check each item off as you pack it.</p>
        @endif
    </main>
</body>
</html>
