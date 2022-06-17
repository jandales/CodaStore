
import { moneyFormatter } from "../../module/money-footer";
import { openSidebarModal } from "../../module/modal"
import { cartCountToElement, updateCartDOMELement } from '../../app/module/Cart';
let cart;

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _delete = "DELETE"

function getCarts(){                         
     $.ajax({
         url : '/get-user-cart',
         method : 'GET',
         async: false,
         success :  function(response){
             cart = response.cart;
         }
     });
}

function deleteCart(id){
     $.ajax({
         url : `/cart/destroy/${id}`,
         method : 'POST',
         data  : {
             _token : _token,
             _method : _delete
         },                  
         success :  function(response){             
            cartCountToElement(response.count)   
            updateTotal(response.total)    
         }
     });
}
 

function cartsToViews(){    
    let wrapperElement = document.createElement('div')
    wrapperElement.classList.add('cart-items-wrapper') 
    if (cart == null) {
        return wrapperElement;
    }
    cart.items.forEach(item => { 
        let cartItemsElement = document.createElement('div')
        cartItemsElement.classList.add('cart-items','gap10')
        let items = `<div class="cart-image">
                        <img class="img" src="${item.product.imagePath}" alt="" srcset="">              
                     </div>
                    <div class="cart-decription">
                        <p class="cart-item-name">${item.product.name}</p>  
                        <span class="cart-item-info">${item.qty}  x  ${item.product.regular_price}</span>  
                        <ul class="cart-item-variant">  
                          
                        </ul> 
                    </div>
                    <span class="cart-remove" data-id="${item.id}">Remove</span>           
            `  
        
        cartItemsElement.innerHTML = items
        let ul = cartItemsElement.querySelector('.cart-item-variant')
            let attributes = JSON.parse(item.attributes);
        if(attributes){
            attributes.forEach(element => {
                ul.innerHTML += `<li><span class="capitalize">${element.name} : ${element.value}</span></li>`             
            });
        }  
        wrapperElement.appendChild(cartItemsElement);
    });  


    return wrapperElement        
}
 
         



 const sidecart = document.querySelector('.sidebar-body')


function clearCartElement()
{
     let wrapper = sidecart.querySelector('.cart-items-wrapper');
     if(wrapper) return wrapper.remove();
   
}

function dispatchRemoveCartEvent(){
     const cartRemoveELement = document.querySelectorAll('.cart-remove');
     cartRemoveELement.forEach(elem => {
         elem.onclick = function(){     
            let id = elem.getAttribute('data-id')
            let parent = elem.closest('.cart-items');
            parent.remove();    
            deleteCart(id)
         }
     })
    
}


function updateTotal(total){
    const sidecartTotal = document.querySelector('.cart-item-total')
    sidecartTotal.innerText =  moneyFormatter(total);  
}
 const modalTrigger1 = document.getElementById('open-side-cart-modal');  
if(modalTrigger1){
    modalTrigger1.addEventListener('click', function(){
        clearCartElement();
        getCarts();
        sidecart.appendChild(cartsToViews())
        const total = cart != null ? cart.total : 0;
        updateTotal(total)
        openSidebarModal('sidecartModal')
        dispatchRemoveCartEvent();
    });
   
}

