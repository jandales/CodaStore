let cartItemSubtotalElemnt;

function cartItemRemoveRequest(cart){

    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content'); 
    $.ajax({                 
            url: `/cart/destroy/${cart}`,
            method: 'post',
            dataType:"json",
            data : { 
                _token : token,
                _method : 'delete'
                },
            success:function(response){ 
                if(response.status === 200)  location.reload();  
                getCartCount(); 
            }
    })
}

function updateCartItemQty(id, qty){

    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
       
        $.ajax({
            url: `/cart/update/${id}`,
            method: 'post',
            data : { 
                _token : token,
                _method : 'PUT',
                quantity : qty
                },
            success:successResponse,
        })
}

function successResponse(response)
{
  
    cartItemSubtotalElemnt.innerHTML = moneyFormatter(response.item_subtotal)
    // document.getElementById('cart-items-count').innerText =  `Items: ${response.items_count}`;
    // document.getElementById('grand-total').innerText =  moneyFormatter(response.grand_total);
    // document.getElementById('subtotal').innerText = moneyFormatter(response.subtotal);
    // document.querySelector('.cart-total').innerText = moneyFormatter(response.item_subtotal);
    getCartCount(); 
}






const cartItemRemoves = document.querySelectorAll('.cart-item-remove');

cartItemRemoves.forEach(element => {
    element.onclick = function() {
        const id = element.getAttribute('data-id'); 
        cartItemRemoveRequest(id);                      
    }
})

const addlessQuantity = document.querySelectorAll('.add-less-quantity');

if (addlessQuantity)
{
    addlessQuantity.forEach(element => {
        element.onclick = function(){

            const type =element.getAttribute('type');        
            let input = element.closest('td').querySelector('.cart-qty')     
            let id = input.getAttribute('item');   

            cartItemSubtotalElemnt =  element.closest('tr').querySelector('.cart-total'); 

            let qty = 0;
            let currentQty = parseInt(input.value);
            if(type == 'less') {                
                qty = currentQty == 1 ? 1 : currentQty - 1; 
            }else{
                qty =  currentQty +  1 ;                    
            }

            input.value = qty;        
            updateCartItemQty(id, qty)
            
        }
    })
}



const selectShippingMethod = document.getElementById('select-shipping-method');

if (selectShippingMethod) {
    selectShippingMethod.onchange = function() {
        const id = this.options[this.selectedIndex].value
        selectShippingMethodRequest(id);
    }
}


function selectShippingMethodRequest(id){
    $.ajax({
        url : `/cart/select/${id}/shipping-method/`,
        type : 'Get',
        async : false,
        success : successShippingResponse, 
    });
}

function successShippingResponse(response)
{
    document.getElementById('shipping-method-description').innerHTML = response.shipping_method.description;
    document.getElementById('shipping-method-amount').innerHTML = moneyFormatter(response.shipping_method.amount);
    document.getElementById('grand-total').innerText =  moneyFormatter(response.grand_total);

}
















