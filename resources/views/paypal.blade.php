@extends('layout.front.app')

@section('content') 
    <div class="container">             
            <a href="{{ route('paypal-process')}}">Paypal</a>               
    </div>
    <div id="paypal-button-container"></div>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      
    @endif
    @if (session('success'))
    <div class="alert alert-success mt-1">{{session('success')}}</div>
    @endif  
    
    <script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_SANDBOX_CLIENT_ID')}}&currency=USD&intent=capture&enable-funding=venmo" data_source="integrationbuilder"></script>
 

	{{-- <script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_SANDBOX_CLIENT_ID')}}"></script> --}}
    <script>
        const paypalButtonsComponent = paypal.Buttons({
            // optional styling for buttons
            // https://developer.paypal.com/docs/checkout/standard/customize/buttons-style-guide/
            style: {
              color: "gold",
              shape: "rect",
              layout: "vertical"
            },

            // set up the transaction
            createOrder: (data, actions) => {
                // pass in any options from the v2 orders create call:
                // https://developer.paypal.com/api/orders/v2/#orders-create-request-body
                const createOrderPayload = {
                    purchase_units: [
                        {
                            amount: {
                                value: "88.44"
                            }
                        }
                    ]
                };

                return actions.order.create(createOrderPayload);
            },

            // finalize the transaction
            onApprove: (data, actions) => {
                const captureOrderHandler = (details) => {
                    const payerName = details.payer.name.given_name;
                    console.log('Transaction completed');
                };

                return actions.order.capture().then(captureOrderHandler);
            },

            // handle unrecoverable errors
            onError: (err) => {
                console.error('An error prevented the buyer from checking out with PayPal');
            }
        });

        paypalButtonsComponent
            .render("#paypal-button-container")
            .catch((err) => {
                console.error('PayPal Buttons failed to render');
            });
      </script>
@endsection


   
    
     