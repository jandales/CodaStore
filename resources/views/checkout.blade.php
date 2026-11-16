<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Checkout | Coda')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
</head>

<body class="checkout-body">
    @php
        $progress = checkoutProgress();
        $steps = [
            ['label' => 'Bag', 'route' => route('cart'), 'reached' => true],
            ['label' => 'Information', 'route' => route('checkout.information'), 'reached' => true],
            ['label' => 'Shipping', 'route' => route('checkout.shipping'), 'reached' => $progress >= 30],
            ['label' => 'Payment', 'route' => route('checkout.payment'), 'reached' => $progress >= 60],
        ];
        $currentIndex = $progress >= 100 ? 4 : ($progress >= 60 ? 3 : ($progress >= 30 ? 2 : 1));
    @endphp
    <header class="checkout-header">
        <div class="checkout-header__inner">
            <a href="{{ route('home') }}" class="wordmark" aria-label="Coda home">Coda</a>
            <span class="checkout-header__secure"><i class="fas fa-lock" aria-hidden="true"></i> Secure checkout</span>
        </div>
    </header>
    <div class="container-lg mx-auto checkout-shell">
        @if ($progress < 100)
            <nav class="checkout-steps" aria-label="Checkout progress">
                <ol>
                    @foreach ($steps as $i => $step)
                        <li class="{{ $i === $currentIndex ? 'is-current' : ($i < $currentIndex ? 'is-done' : '') }}">
                            @if ($i < $currentIndex)
                                <a href="{{ $step['route'] }}">{{ $step['label'] }}</a>
                            @else
                                <span @if($i === $currentIndex) aria-current="step" @endif>{{ $step['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        {{-- Kept for the JS progress module; hidden by CSS. --}}
        <div class="step-progress-wrapper checkout-progress-legacy" aria-hidden="true">
            <div style="--width:{{ $progress }};" class="progress-bar"></div>
        </div>
        @if (!request()->is('checkout/completed'))
            <div class="sm-visible checkout-summary-toggle">
                <button type="button" id="open-cart-summary" class="change">
                    <i class="fas fa-shopping-bag" aria-hidden="true"></i>
                    Show order summary
                </button>
            </div>
        @endif
        @yield('content')
    </div>

    <div class="modal" id="shipping-address-modal">
        <div class="modal-sidebar">
            <div class="sidebar-close">                    
                <span class="close-drawer"><i class="fas fa-times"></i></span>
            </div>         
            <div class="sidebar-heading">
                <p>Shipping Address</p>
            </div>
            <div class="sidebar-body">
                @if (shippingAddress()->count() != 0)
                    <form id="form-shipping-address" method="post">
                        @csrf          
                        @method('PUT')    
                        @foreach (shippingAddress() as $item)        
                                <div class="address-item {{$item->status == 1 ? 'active' : ''}}">
                                    <input type="radio" name="selected"  class="shipping-address-input"  value="{{route('account.shippingaddress.update-status',[ $item->id ])}}" {{$item->status == 1 ? 'checked' : ''}}>
                                    <ul>
                                        <li>{{ $item->fullName() }}</li>
                                        <li>{{ $item->fullAddress() }}</li>
                                    </ul>
                                </div>                 
                        @endforeach
                        <button id="confirm-shipping-address" class="btn btn-success w-12" >Confirm</button>
                    </form> 
                @else
                        <label class="block text-center">No saved addresses</label>
                @endif
               
                
            </div>
           
            <div class="sidebar-footer">
                
            </div>
        </div> 
    </div>
    <div class="modal" id="payment-option-modal">
        <div class="modal-sidebar">
            <div class="sidebar-close">                    
                <span class="close-drawer"><i class="fas fa-times"></i></span>
            </div>         
            <div class="sidebar-heading">
                <p>Payment method</p>
            </div>
            <div class="sidebar-body">
                @if (paymentOptions()->count() != 0)
                    <form id="form-payment-option" method="post">
                        @csrf          
                        @method('PUT')    
                        @foreach (paymentOptions() as $item)        
                                <div class="payment-option-item {{$item->status == 1 ? 'active' : ''}}">
                                    <input type="radio" name="selected"  class="payment-option-input"  value="{{route('account.payment-option.update.status',[ $item->id ])}}" {{$item->status == 1 ? 'checked' : ''}}>
                                    <ul>
                                        <li>{{ $item->card_name }}</li>
                                        <li>{{ $item->card_number }}</li>
                                    </ul>
                                </div>                 
                        @endforeach
                        <button id="confirm-payment-option" class="btn btn-success w-12" >Confirm</button>
                    </form>
                @else
                    <label class="block text-center">No saved payment methods</label>
                @endif
               
            </div>
           
            <div class="sidebar-footer">
                
            </div>
        </div> 
    </div>

    <div class="spinner-wrapper">
        <div class="spinner-container">
           <div class="spinner"></div>
           <div class="loading-success">
               <span><i class="fa fa-check"></i></span>
               <a href="#" id="btn-payment-completed">Payment Completed</a>
           </div>
           {{-- <div class="loading-failed">
               <span>Server Error</span>
               <a>Ok</a>
           </div> --}}
        </div>            
   </div>

</body>
<script src="/js/jquery.min.js"></script>
<script src="{{ asset('js/app.js')}}"></script>
</html>