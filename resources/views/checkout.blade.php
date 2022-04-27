<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apprael Store</title>  
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">  
    <meta name="csrf-token" content="{{ csrf_token() }}">    
    
</head>

<body>
    <div class="container-lg mx-auto">
            <h1 class="block text-center mt-4 text-2xl">CodaStore</h1>      
        <div class="step-progress-wrapper w-6">
            <div class="step-circles">
                <div class="step {{ checkoutProgress() == 0 || checkoutProgress() == 30 ||  checkoutProgress() == 60 ||  checkoutProgress() == 100 ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-info"></i></div>
                    <strong>Information</strong>
                </div>

                <div class="step {{ checkoutProgress() == 30  || checkoutProgress() == 60 || checkoutProgress() == 100   ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-shipping-fast"></i></div>
                    <strong>Shipping</strong>
                </div>

                <div class="step {{ checkoutProgress() == 60  ||  checkoutProgress() == 100 ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-credit-card"></i></div>
                    <strong>Payment</strong>
                </div>  
                
                <div class="step {{ checkoutProgress() == 100  ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-credit-card"></i></div>
                    <strong>Completed</strong>
                </div>   
            </div>
            <div style="--width:{{ checkoutProgress() }};" class="progress-bar"></div>       
        </div>
      
        @yield('content')
    </div>
</body>
<script src="/js/front/jquery.min.js"></script>




<script type="module">

import { errorReponse, errorRemove } from '/js/validator.js';

const btncheckoutInfo = document.getElementById('btn-continue');

 if(btncheckoutInfo){
     btncheckoutInfo.onclick = submit;
 }

function submit(e){
    e.preventDefault();
    const form = document.getElementById('form');
    const url = form.getAttribute('action');
    
    $.ajax({
        url : url,
        type : 'POST',
        data :  new FormData(form),
        processData: false,
        contentType: false,
        cache: false,        
        error : errorReponse,
        success : redirectResponse,
    })
}

function redirectResponse(res){
    if (res.status === 200)
        window.location.href = res.url;
}



function moneyFormatter(number){
    
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
    });

    return formatter.format(number);

}

function updateCartInfo(id){ 
    let res = getShippingMethod(id) 
    let cart = getCart();  
    const shippingcharge =  res.shipping_method.amount; 
    const total =  (cart.total - cart.discount) + shippingcharge;
    document.querySelector('.span-shipping-fee').innerHTML = moneyFormatter(shippingcharge);
    document.getElementById('grand-total').innerHTML = moneyFormatter(total);
   
}


function updateCartTotal(cart, coupon = 0, ){
    let total = 0;
    total =  total + coupon;
}

function getShippingMethod(id){
    let response;
    $.ajax({
        url : `/get-shipping-method/${id}`,
        type : 'get',
        async : false,
        success :function(res) {
             response = res;              
        }
    })
    return response;
}

function getCart(){
    let response;
    $.ajax({
        url : `/get-user-cart`,
        type : 'get',
        async : false,
        success :function(res) {        
             response = res.cart;              
        }
    })
    return response;
}



const inputRadios = document.querySelectorAll('input[type="radio"]');
if(inputRadios){
    inputRadios.forEach(element => {
        element.onchange =  function() {
            updateCartInfo(element.value)
        }
    })
}


//coupon

    function activateCoupon(e){
        e.preventDefault();
        const couponCode = document.querySelector('input[name="coupon_code"]').value;
        $.ajax({
            url: `/coupon/activate`,
            method: 'get',       
            data : {coupon_code : couponCode},
            error: errorReponse,
            success: function(response){                
                console.log(response);
                document.querySelector('.coupon_code').innerHTML = response.coupon.name;
                document.querySelector('.span-coupon-amount').innerHTML = moneyFormatter(response.coupon.amount);
            }
        })
    }


const btncoupon = document.getElementById('btn-coupon-apply');
btncoupon.onclick = activateCoupon;



</script>


<script src="/js/front/cart/cart.js"></script>
<script type="module" src="/js/front/cart/coupon.js"></script>


</html>