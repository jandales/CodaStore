@extends('checkout')
@section('content')


        <div class="flex  space-between gap50 mt-5 sm-flex-column md-mt-2">
            <div class="w-6 sm-w-12">

               <x-checkout-information-component/>

                @if (session('error'))
                    <div class="alert alert-danger mt-2" role="alert">{{ session('error') }}</div>
                @endif
                @if (session('message'))
                    <div class="alert alert-warning mt-2" role="status">{{ session('message') }}</div>
                @endif

                @php
                    $codOffered = $codEnabled && !$codReason;
                    $chosenMethod = old('payment_method', 'paypal') === 'cod' && $codOffered ? 'cod' : 'paypal';
                @endphp
                <form id="form-payment" class="pay-form" action="{{ route('checkout.place') }}" method="post">
                    @csrf
                    <fieldset class="checkout-shipping-method mt-2 pay-methods">
                        <legend class="checkout-section-title">Payment</legend>

                        <label class="pay-option" for="pm-paypal">
                            <input type="radio" id="pm-paypal" name="payment_method" value="paypal" {{ $chosenMethod === 'paypal' ? 'checked' : '' }}>
                            <i class="fab fa-paypal pay-option__icon" aria-hidden="true"></i>
                            <span class="pay-option__text">
                                <strong>PayPal</strong>
                                <span>Pay with your PayPal balance or any debit or credit card. You'll return here to see your order. We never see your card details.</span>
                            </span>
                        </label>

                        @if ($codEnabled)
                            <label class="pay-option {{ $codOffered ? '' : 'is-disabled' }}" for="pm-cod">
                                <input type="radio" id="pm-cod" name="payment_method" value="cod" {{ $chosenMethod === 'cod' ? 'checked' : '' }} {{ $codOffered ? '' : 'disabled' }} @if (!$codOffered) aria-describedby="cod-reason" @endif>
                                <i class="fas fa-money-bill-wave pay-option__icon" aria-hidden="true"></i>
                                <span class="pay-option__text">
                                    <strong>Cash on delivery @if ($codFee > 0)<span class="pay-option__fee">+@money($codFee) fee</span>@endif</strong>
                                    @if ($codOffered)
                                        <span>Pay in cash when your order arrives. Please have the exact amount ready.</span>
                                    @else
                                        <span id="cod-reason">{{ $codReason }}</span>
                                    @endif
                                </span>
                            </label>
                        @endif
                    </fieldset>

                    <div class="checkout-shipping-method mt-2">
                        <h2 class="checkout-section-title">Billing address</h2>
                        <span class="block mt10">Select the address that matches your payment method.</span>
                        <div class="panel border-b-0 mt-1">
                            <div class="flex gap10">
                                <input type="radio" id="use-same" name="same_as_shipping" {{ old('is_new_billing') == 1 ? '' : 'checked' }} value="0">
                                <label for="use-same">Same as shipping address</label>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="flex gap10">
                                <input type="radio" id="use-differ" name="is_new_billing" value="1" {{ old('is_new_billing') == 1 ? 'checked' : '' }}>
                                <label for="use-differ">Use a different address</label>
                            </div>
                            <x-form-billing-component/>
                        </div>
                    </div>

                    <div class="form-block mt-2">
                        <button type="submit" id="pay-with-paypal" class="btn btn-dark mr-1 md-mr-0 pay-btn pay-btn--paypal"><i class="fab fa-paypal" aria-hidden="true"></i> Pay with PayPal</button>
                        @if ($codEnabled && $codOffered)
                            <button type="submit" id="place-cod-order" class="btn btn-dark mr-1 md-mr-0 pay-btn pay-btn--cod">Place order{{ $codFee > 0 ? ' (+' . '₱' . number_format($codFee, 2) . ' COD fee)' : '' }}</button>
                        @endif
                        <a href="{{ route('checkout.shipping') }}" class="change sm-text-center"><i class="fas fa-arrow-left" aria-hidden="true"></i> Return to shipping</a>
                    </div>
                </form>


            </div>
            <div class="w-6 sm-w-12">
                <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>
            </div>
        </div>








@endsection




     