@extends('layout.admin')

@section('title', 'Payment methods | Coda Admin')

@section('content')
<div class="page-title">
    <h1>Payment methods</h1>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<section class="panel payment-method-card" aria-labelledby="paypal-title">
    <div class="panel-body">
        <h2 id="paypal-title" class="dash-card__title"><i class="fab fa-paypal" aria-hidden="true"></i> PayPal</h2>
        <p class="form-help">Always on. Customers pay with their PayPal balance or a card. Keys are set in the server's <code>.env</code> file.</p>
    </div>
</section>

<form method="post" action="{{ route('admin.setting.payments.update') }}" class="panel payment-method-card mt20">
    @csrf
    @method('put')
    <div class="panel-body">
        <h2 class="dash-card__title"><i class="fas fa-money-bill-wave" aria-hidden="true"></i> Cash on delivery</h2>
        <label class="sale-toggle"><input type="checkbox" name="cod_enabled" value="1" {{ old('cod_enabled', $settings->cod_enabled) ? 'checked' : '' }}> Offer cash on delivery at checkout</label>

        <div class="cod-settings-grid mt20">
            <div class="form-block">
                <label for="cod_fee">COD fee (₱)</label>
                <input type="number" id="cod_fee" name="cod_fee" min="0" max="10000" step="0.01" value="{{ old('cod_fee', number_format((float) $settings->cod_fee, 2, '.', '')) }}">
                <p class="form-help">Added to the order total when the customer picks cash on delivery. Use 0 for no fee.</p>
            </div>
            <div class="form-block">
                <label for="cod_max_total">Highest order total (₱)</label>
                <input type="number" id="cod_max_total" name="cod_max_total" min="0" step="0.01" value="{{ old('cod_max_total', $settings->cod_max_total !== null ? number_format((float) $settings->cod_max_total, 2, '.', '') : '') }}" placeholder="No limit">
                <p class="form-help">Bigger orders must be paid with PayPal. Leave empty for no limit.</p>
            </div>
            <div class="form-block">
                <label for="cod_max_open_orders">Unpaid COD orders per customer</label>
                <input type="number" id="cod_max_open_orders" name="cod_max_open_orders" min="0" max="50" step="1" value="{{ old('cod_max_open_orders', $settings->cod_max_open_orders) }}">
                <p class="form-help">How many COD orders one customer can have waiting for delivery. 0 means no limit.</p>
            </div>
        </div>

        @php $chosen = old('cod_shipping_methods', $settings->cod_shipping_methods ?? []); @endphp
        <fieldset class="access-fieldset">
            <legend>Shipping methods that allow cash on delivery</legend>
            <p class="form-help">Leave all unticked to allow every shipping method.</p>
            <div class="access-permissions">
                @forelse ($shippingMethods as $method)
                    <label class="access-permission">
                        <input type="checkbox" name="cod_shipping_methods[]" value="{{ $method->id }}" {{ in_array($method->id, array_map('intval', (array) $chosen), true) ? 'checked' : '' }}>
                        <span><strong>{{ $method->name }}</strong><small>@money($method->amount)</small></span>
                    </label>
                @empty
                    <p class="form-help">No shipping methods yet.</p>
                @endforelse
            </div>
        </fieldset>

        <button class="btn btn-primary">Save</button>
    </div>
</form>
@endsection
