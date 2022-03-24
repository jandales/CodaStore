

const btnPlaceOrder = document.getElementById('placeOrder');


let payment_method = "";
let btnPaymentMethod = document.querySelectorAll(".payment-option")

btnPaymentMethod.forEach(option => {              
    option.addEventListener('click', function(){   
        resetOption()                 
        option.classList.add("selected")      
        payment_method = option.getAttribute("method") 
        paymentMethodInput = document.querySelector('input[name="payment_method"]');
        paymentMethodInput.value = payment_method;   
    
    })
 
});

function resetOption(){
    btnPaymentMethod.forEach(option => {
        option.classList.remove("selected")
    })
}

btnPlaceOrder.addEventListener('click', function(e){

    if(payment_method == "") 
    {
        showMessage('danger', "Please Select Payment method")
        e.preventDefault()
        return
    }

 
    const form =  document.getElementById('placeorderForm');
    let amount = document.getElementById('total').innerHTML;
    amountElementInput = document.querySelector('input[name="amount"]');
    amountElementInput.value = amount
    
    e.preventDefault()
    form.submit();

});


function removeItem(cart)
{   
    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');    
    $.ajax({
        url : `/cart/checkout/remove/${cart}`,
        method : 'POST',
        data : {
            _token : token            
        },
        success : function(response) {   
            if(response.status != 200) return;            
            location.reload(); 
        }
    })
}