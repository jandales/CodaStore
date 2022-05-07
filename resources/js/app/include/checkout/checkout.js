
import { isBoolean } from 'lodash';
import { errorReponse, errorRemove } from '../../../module/validator';

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



const inputRadios = document.querySelectorAll('.shipping-input');
if(inputRadios){
    inputRadios.forEach(element => {
        element.onchange =  function() {
            updateCartInfo(element.value)
        }
    })
}




///side model shiffing address

import { openSidebarModal } from '../../../module/modal'
const openModalShipping = document.getElementById('open-modal-shipping');

const shippingAddressList = document.querySelectorAll('.shipping-address-input');
if(openModalShipping){
    openModalShipping.onclick = function(){
        openSidebarModal('shipping-address-modal');
    }    
}

shippingAddressList.forEach(elem => {   
    elem.onclick=  function(){
       const addressItem = elem.parentElement;
       currentSelectedAddress().classList.remove('active');
       addressItem.classList.add('active');    
    }
})

function currentSelectedAddress(){
    const shippingaddressmodal =  document.getElementById('shipping-address-modal');
    if(shippingaddressmodal){
        return shippingaddressmodal.querySelector('.address-item.active')        
    }
}

const confirmShippingAddress = document.getElementById('confirm-shipping-address');

if(confirmShippingAddress){ 
    confirmShippingAddress.onclick= function(e) {
        e.preventDefault();    
        const form = document.getElementById('form-shipping-address'); 
        const input = currentSelectedAddress().querySelector('input[type="radio"]');
        const route = input.value;
        form.setAttribute('action',route);   
        form.submit();     
    }
}
