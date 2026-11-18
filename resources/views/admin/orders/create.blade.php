@extends('layout.admin')

@section('title', 'Create order | Coda Admin')

@php
    // Lines kept after a validation error, with the product details the script needs.
    $oldLines = collect(old('lines', []))->map(function ($line) {
        $product = \App\Models\Product::with('skus')->find($line['product_id'] ?? 0);
        if (!$product) return null;
        $sku = !empty($line['sku_id']) ? $product->skus->firstWhere('id', (int) $line['sku_id']) : null;
        return [
            'product_id' => $product->id, 'name' => $product->name, 'image' => $sku && $sku->image ? $sku->image : $product->imagePath,
            'sku_id' => optional($sku)->id, 'variant' => optional($sku)->label(),
            'price' => $sku ? $sku->effectivePrice() : $product->effectivePrice(),
            'stock' => $sku ? (int) $sku->qty : $product->stockQty(),
            'qty' => (int) ($line['qty'] ?? 1),
        ];
    })->filter()->values();
@endphp

@section('content')
<div class="page-title order-page-title">
    <h1>Create order</h1>
    <a href="{{ route('admin.orders') }}" class="btn btn-light">All orders</a>
</div>

@if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Please check the order:</strong>
        <ul class="mo-errors">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="post" action="{{ route('admin.orders.manual.store') }}" id="manual-order" class="mo-layout"
      data-products-url="{{ route('admin.orders.lookup.products') }}"
      data-customers-url="{{ route('admin.orders.lookup.customers') }}"
      data-old-lines="{{ $oldLines->toJson() }}">
    @csrf
    <div class="mo-main">
        {{-- Customer --}}
        <section class="panel mo-card" aria-labelledby="mo-customer">
            <div class="panel-body">
                <h2 id="mo-customer" class="dash-card__title">Customer</h2>
                <div class="mo-toggle" role="radiogroup" aria-label="Customer">
                    <label><input type="radio" name="customer_mode" value="existing" {{ old('customer_mode', 'existing') === 'existing' ? 'checked' : '' }}> Existing customer</label>
                    <label><input type="radio" name="customer_mode" value="new" {{ old('customer_mode') === 'new' ? 'checked' : '' }}> New customer</label>
                </div>

                <div data-mode="existing">
                    <input type="hidden" name="user_id" value="{{ old('user_id') }}">
                    <div class="mo-search">
                        <label for="customer-search" class="sr-only">Search customers</label>
                        <input type="search" id="customer-search" placeholder="Search by name, email or phone" autocomplete="off">
                        <ul class="mo-results" data-customer-results role="listbox" hidden></ul>
                    </div>
                    <div class="mo-chosen" data-customer-chosen hidden></div>
                </div>

                <div data-mode="new" hidden>
                    <div class="form-block">
                        <label for="email">Email <span class="label-hint">(optional)</span></label>
                        <input type="email" id="email" name="email" maxlength="191" value="{{ old('email') }}">
                        <p class="form-help">With an email, a customer account is created so they can see the order and get emails. Leave it empty for a guest order (name, phone and address only).</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Address --}}
        <section class="panel mo-card" aria-labelledby="mo-address">
            <div class="panel-body">
                <h2 id="mo-address" class="dash-card__title">Delivery address</h2>
                <div class="mo-grid">
                    @foreach ([['firstname', 'First name'], ['lastname', 'Last name'], ['phone', 'Phone'], ['street', 'Street address'], ['city', 'City'], ['region', 'Region / province'], ['zipcode', 'ZIP code'], ['country', 'Country']] as [$field, $label])
                        <div class="form-block {{ $field === 'street' ? 'mo-wide' : '' }}">
                            <label for="addr-{{ $field }}">{{ $label }}</label>
                            <input type="text" id="addr-{{ $field }}" name="{{ $field }}" value="{{ old($field, $field === 'country' ? 'Philippines' : '') }}" {{ $field !== 'country' ? 'required' : '' }}>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Items --}}
        <section class="panel mo-card" aria-labelledby="mo-items">
            <div class="panel-body">
                <h2 id="mo-items" class="dash-card__title">Items</h2>
                <div class="mo-search">
                    <label for="product-search" class="sr-only">Search products</label>
                    <input type="search" id="product-search" placeholder="Search products by name or SKU" autocomplete="off">
                    <div class="mo-results mo-results--products" data-product-results hidden></div>
                </div>
                <table class="table mo-lines" data-lines hidden>
                    <thead>
                        <tr><th scope="col">Item</th><th scope="col">Price</th><th scope="col">Qty</th><th scope="col" class="text-right">Total</th><th scope="col"><span class="sr-only">Remove</span></th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <p class="form-help" data-no-lines>Search for a product to add it. Prices come from the catalogue (variant prices and sales included).</p>
            </div>
        </section>

        {{-- Shipping and discount --}}
        <section class="panel mo-card" aria-labelledby="mo-shipping">
            <div class="panel-body">
                <h2 id="mo-shipping" class="dash-card__title">Shipping and discount</h2>
                <div class="mo-grid">
                    <div class="form-block">
                        <label for="shipping_method_id">Shipping method</label>
                        <select id="shipping_method_id" name="shipping_method_id" required>
                            @foreach ($shippingMethods as $method)
                                <option value="{{ $method->id }}" data-amount="{{ $method->amount }}" {{ (int) old('shipping_method_id') === $method->id ? 'selected' : '' }}>{{ $method->name }} (@money($method->amount))</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-block">
                        <label for="shipping_charge">Shipping fee (₱)</label>
                        <input type="number" id="shipping_charge" name="shipping_charge" min="0" step="0.01" required value="{{ old('shipping_charge', optional($shippingMethods->first())->amount ?? 0) }}">
                        <p class="form-help">Use 0 for pickup.</p>
                    </div>
                    <div class="form-block">
                        <label for="discount_value">Order discount</label>
                        <div class="mo-inline">
                            <input type="number" id="discount_value" name="discount_value" min="0" step="0.01" value="{{ old('discount_value') }}" placeholder="0">
                            <select name="discount_type" aria-label="Discount type">
                                <option value="amount" {{ old('discount_type', 'amount') === 'amount' ? 'selected' : '' }}>₱</option>
                                <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>%</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Payment --}}
        <section class="panel mo-card" aria-labelledby="mo-payment">
            <div class="panel-body">
                <h2 id="mo-payment" class="dash-card__title">Payment</h2>
                <div class="mo-toggle mo-toggle--stack" role="radiogroup" aria-label="Payment">
                    <label><input type="radio" name="payment" value="cod" {{ old('payment', 'cod') === 'cod' ? 'checked' : '' }}> <span><strong>Cash on delivery</strong><small>The courier collects the cash; marking the order delivered records it.</small></span></label>
                    <label><input type="radio" name="payment" value="paid" {{ old('payment') === 'paid' ? 'checked' : '' }}> <span><strong>Paid</strong><small>Cash, bank transfer, GCash or Maya already received.</small></span></label>
                    <label><input type="radio" name="payment" value="awaiting" {{ old('payment') === 'awaiting' ? 'checked' : '' }}> <span><strong>Awaiting payment</strong><small>For example waiting for a bank transfer. Use "Mark as paid" on the order later.</small></span></label>
                </div>
                <div class="mo-grid" data-payment-details>
                    <div class="form-block">
                        <label for="payment_method">Paid with</label>
                        <select id="payment_method" name="payment_method">
                            <option value="">Choose…</option>
                            @foreach ($paymentMethods as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-block">
                        <label for="payment_reference">Reference <span class="label-hint">(optional)</span></label>
                        <input type="text" id="payment_reference" name="payment_reference" maxlength="100" value="{{ old('payment_reference') }}" placeholder="e.g. GCash reference no.">
                    </div>
                </div>
            </div>
        </section>

        {{-- Details --}}
        <section class="panel mo-card" aria-labelledby="mo-details">
            <div class="panel-body">
                <h2 id="mo-details" class="dash-card__title">Details</h2>
                <div class="mo-grid">
                    <div class="form-block">
                        <label for="channel">Order came from</label>
                        <select id="channel" name="channel" required>
                            @foreach ($channels as $key => $label)
                                <option value="{{ $key }}" {{ old('channel', 'facebook') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-block">
                        <label for="order_date">Order date</label>
                        <input type="date" id="order_date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" min="{{ now()->subDays($maxBackdate)->toDateString() }}">
                        <p class="form-help">Up to {{ $maxBackdate }} days back, for orders taken earlier.</p>
                    </div>
                    <div class="form-block mo-wide">
                        <label for="note">Internal note <span class="label-hint">(optional, customers never see it)</span></label>
                        <textarea id="note" name="note" rows="2" maxlength="2000">{{ old('note') }}</textarea>
                    </div>
                </div>
                <label class="sale-toggle"><input type="checkbox" name="send_email" value="1" {{ old('send_email', '1') ? 'checked' : '' }}> Email the customer an order confirmation (when there is an email)</label>
            </div>
        </section>
    </div>

    {{-- Summary --}}
    <aside class="mo-side">
        <section class="panel mo-card mo-summary" aria-labelledby="mo-summary">
            <div class="panel-body">
                <h2 id="mo-summary" class="dash-card__title">Summary</h2>
                <dl>
                    <div><dt>Items (<span data-sum-count>0</span>)</dt><dd data-sum-items>₱0.00</dd></div>
                    <div><dt>Shipping</dt><dd data-sum-shipping>₱0.00</dd></div>
                    <div><dt>Discount</dt><dd data-sum-discount>−₱0.00</dd></div>
                    <div class="mo-total"><dt>Total</dt><dd data-sum-total>₱0.00</dd></div>
                </dl>
                <p class="form-help">Totals are checked again when the order is saved.</p>
                <button type="submit" class="btn btn-primary mo-submit">Create order</button>
            </div>
        </section>
    </aside>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-manual-order.js') }}?v=2"></script>
@endpush
