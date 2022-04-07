<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apprael Store</title>  
    <link rel="stylesheet" href="/css/styles.css">   
    <link rel="stylesheet" href="/fonts/fontawesome-free-5.15.3-web/css/all.css">   
    
</head>

<body>
    <div class="container-lg mx-auto">
            <h1 class="block text-centered mt-4 text-2xl">CodaStore</h1>
        <div class="step-progress-wrapper w-6">
            <div class="step-circles">
                <div class="step {{ checkoutProgress() == 25 || checkoutProgress() == 50 ||  checkoutProgress() == 70 ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-info"></i></div>
                    <strong>Information</strong>
                </div>

                <div class="step {{ checkoutProgress() == 50 ||  checkoutProgress() == 70  ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-shipping-fast"></i></div>
                    <strong>Shipping</strong>
                </div>

                <div class="step">
                    <div class="circle"><i class="fas fa-credit-card"></i></div>
                    <strong>Payment</strong>
                </div>               
            </div>
            <div style="--width:{{ checkoutProgress() }};" class="progress-bar"></div>       
        </div>
      
        @yield('content')
    </div>
</body>

</html>