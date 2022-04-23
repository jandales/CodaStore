

let cart;



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
             console.log(response);             
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
                <img class="img" src="/${item.product.imagePath}" alt="" srcset="">
                <div class="cart-image-overlay flex-vert-center">
                    <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="cart-decription">
                <p class="cart-item-name">${item.product.name}</p>  
                <span class="cart-item-info">${item.qty}  x  ${item.product.regular_price}</span>  
                <ul class="cart-item-variant">    
                </ul> 
            </div>`  
        
        cartItemsElement.innerHTML = items
        let ul = cartItemsElement.querySelector('.cart-item-variant')

        if(item.properties){
            item.properties.forEach(element => {
                ul.innerHTML += `<li><span class="capitalize">${element.name}: ${element.value}</span></li>`             
            });
        }   
        wrapperElement.appendChild(cartItemsElement);
    });  
    return wrapperElement        
}
 
         



 const sidecart = document.querySelector('.sidebar-body')
 const sidecartTotal = document.querySelector('.cart-item-total')

function clearCartElement()
{
     let wrapper = sidecart.querySelector('.cart-items-wrapper');
     if(wrapper) return wrapper.remove();
   
}

function cartRemove(elem){
     let id = elem.getAttribute('data-id')
     let parent = elem.closest('.cart-items');
     parent.remove();    
     deleteCart(id)
}

 const modalTrigger1 = document.querySelector("[data-modal-target]");  

 modalTrigger1.addEventListener('click', function(){
     clearCartElement();
     getCarts();
     sidecart.appendChild(cartsToViews())
     const total = cart != null ? cart.total : 0;
     sidecartTotal.innerHTML =  moneyFormatter(total);

     let id = this.getAttribute("data-modal-target")
     openSidebarModal(id)
 });

