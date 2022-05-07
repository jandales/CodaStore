import { errorReponse, errorRemove } from "../../../module/validator";
import { moneyFormatter } from  "../../../module/money-footer";
const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function activateCoupon(e){
    e.preventDefault();
    const couponCode = document.querySelector('input[name="coupon_code"]').value;
    $.ajax({
        url: `/cart/coupon/activate`,
        method: 'get',       
        data : {coupon_code : couponCode},
        error: errorReponse,
        success: successCouponResponse,
    })
}

function successCouponResponse(response){
  
    if (response.status != 200) return;
    console.log(response);
    couponElementsState('show');
    document.getElementById('coupon-code').innerHTML = response.coupon.name;
    document.getElementById('coupon-amount').innerHTML = moneyFormatter(response.discount);
    document.getElementById('coupon-description').innerHTML =  response.coupon.description;  
    document.querySelector('input[name="coupon_code"]').value = null;    
    document.getElementById('grand-total').innerHTML =  moneyFormatter(response.grand_total);
  
}

function removeCoupon(e){ 
    e.preventDefault();
    $.ajax({
        url: `/cart/coupon/remove`,
        method: 'POST',       
        data : {
            _token : _token,
            _method : 'PUT',
        },
        error: errorReponse,
        success: successCouponRemoveResponse,
    })
}

function successCouponRemoveResponse(response){
    if(response.status != 200) return;
    
    couponElementsState('hidden');   
    document.getElementById('grand-total').innerHTML =   moneyFormatter(response.grand_total);
}

function couponElementsState(state){ 
    if(state == 'hidden') {
        document.getElementById('has-coupon').classList.add('hidden');
        document.getElementById('coupon-form').classList.remove('hidden');
        return;
    }
    document.getElementById('has-coupon').classList.remove('hidden');
    document.getElementById('coupon-form').classList.add('hidden');    
}


const btncoupon = document.getElementById('btn-coupon-apply');
const btncouponRemove = document.getElementById('btn-coupon-remove');
if(btncoupon){
    btncoupon.onclick = activateCoupon;
}
if(btncouponRemove){
    btncouponRemove.onclick = removeCoupon;
}






