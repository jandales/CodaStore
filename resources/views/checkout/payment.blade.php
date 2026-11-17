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

                <form id="form-payment" action="{{ route('checkout.paypal.start') }}" method="post">
                    @csrf
                    <div class="checkout-shipping-method mt-2">
                        <h2 class="checkout-section-title">Payment</h2>
                        <span class="block mt10 checkout-hint"><i class="fas fa-lock" aria-hidden="true"></i> You'll pay securely on PayPal. We never see or store your card details.</span>
                        <div class="paypal-box mt-1">
                            <i class="fab fa-paypal" aria-hidden="true"></i>
                            <div>
                                <strong>PayPal</strong>
                                <span>Pay with your PayPal balance or any debit or credit card. You'll return here to see your order.</span>
                            </div>
                        </div>
                    </div>

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
                        <button type="submit" id="pay-with-paypal" class="btn btn-dark mr-1 md-mr-0"><i class="fab fa-paypal" aria-hidden="true"></i> Pay with PayPal</button>
                        <a href="{{ route('checkout.shipping') }}" class="change sm-text-center"><i class="fas fa-arrow-left" aria-hidden="true"></i> Return to shipping</a>
                    </div>
                </form>
              
               
            </div>            
            <div class="w-6 sm-w-12">
                <x-small-cart-component :cart="$cart" :shippingcharge="session('shipping_method')['amount']"/>               
            </div>
        </div>


   
        




@endsection


   
    
     