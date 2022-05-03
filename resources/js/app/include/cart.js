import { moneyFormatter } from "../../module/money-footer";
import { cartCountToElement, updateCartDOMELement } from "../module/Cart";
let cartItemSubtotalElemnt;
let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function cartItemRemoveRequest(cart){ 
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
                updateCartDOMELement();
            }
    })
}

function updateCartItemQty(id, qty){       
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
    cartCountToElement(response.items_count);
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
















