import { functionsIn, identity, set } from 'lodash';
import { errorReponse, errorRemove } from '../../../module/validator';   
import { openSidebarModal } from '../../../module/modal';  
import { startSpin, stopSpin, spinCompleted } from '../../module/spinner';   
            
const usediffer = document.getElementById('use-differ');
const usesame = document.getElementById('use-same');
const payNow = document.getElementById('paynow');


if (usediffer){
    usediffer.onclick = function() {
        document.getElementById('use-same').checked = false;
        this.parentElement.classList.add('hidden');    
        document.getElementById('form-shipping').classList.remove('hidden');       
    }
}

if (usesame) {
    usesame.onclick =  function(){
        this.checked = true;
        const usediffer = document.getElementById('use-differ');
        usediffer.checked = false;
        usediffer.parentElement.classList.remove('hidden')    
        document.getElementById('form-shipping').classList.add('hidden');  
    }
}

function submitProggress(){

     var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function(e) {   
            startSpin();
       }, false);     
       return xhr;
    
}
if (payNow) {
    payNow.onclick = function(e) {
        e.preventDefault();
        const form = document.getElementById('form');
        const url = form.getAttribute('action');
        let formData =  new FormData(form);         
        $.ajax({
            xhr: submitProggress ,
            url : url,
            type : 'POST',
            data : formData,
            datatype:"json", 
            processData: false,
            contentType: false,
            cache: false,
            error : function(res){             
                errorReponse(res);  
                stopSpin()
                if(res.status === 500){
                    window.location.href = '/server-error';
                }             
            },

            success : function(res){                      
                errorRemove();
                if (res.status === 200){  
                    spinCompleted(true,  res.route);
                }
               
                  
            }
        })
    }
}


const paymentOptionChange = document.getElementById('payment-option-change');
const paymentOptionInput = document.querySelectorAll('.payment-option-input');
const confirmPaymentOption = document.getElementById('confirm-payment-option');

function SelectedPaymentOption(){
    const shippingaddressmodal =  document.getElementById('payment-option-modal');
    if(shippingaddressmodal){
        return shippingaddressmodal.querySelector('.payment-option-item.active')        
    }
}

if (paymentOptionChange){
    paymentOptionChange.onclick = function(){
        openSidebarModal('payment-option-modal');
    }
}

paymentOptionInput.forEach(elem => {   
    elem.onclick=  function(){
       const paymentOptionItem = elem.parentElement;
       SelectedPaymentOption().classList.remove('active');
       paymentOptionItem.classList.add('active');    
    }
})

if (confirmPaymentOption) {
    confirmPaymentOption.onclick = function(e){
        const form = document.getElementById('form-payment-option'); 
        const input = SelectedPaymentOption().querySelector('input[type="radio"]');
        const route = input.value;
        form.setAttribute('action',route);   
        form.submit(); 
    }
}
