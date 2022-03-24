

let carts = []
let total = 0

 function getCarts(){                         
     $.ajax({
         url : '/api/carts',
         method : 'GET',
         async: false,
         success :  function(response){
             carts = response.carts   
             total = response.total                  
         }
     });
 }

 function deleteCart(id){
     $.ajax({
         url : `/cart/destroy/${id}'`,
         method : 'POST',
         data  : {
             _token : _token,
             _method : _delete
         },                  
         success :  function(response){               
         }
     });
 }

 
//  function cartsToViews(){  
//      let items = ""     
 
//      carts.forEach(item => { 
           
//         console.log(item.hasOwnProperty(properties))      
//          items += `<div class="cart-items">
//              <div class="cart-image">
//                  <img class="img" src="/${item.product.imagePath}" alt="" srcset="">
//                  <div class="cart-image-overlay flex-vert-center">
//                      <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
//                  </div>
//              </div>
//              <div class="cart-decription">
//                  <p class="cart-item-name">${item.product.name}</p>  
//                  <span class="cart-item-info">${item.qty}  x  ${item.price}</span>  
//                  <ul class="cart-item-variant">    
//                  </ul> 
//              </div> 
//          </div>`        
//      });  

  
//      return items                
//  }

function cartsToViews(){    
    let wrapperElement = document.createElement('div')
    wrapperElement.classList.add('cart-items-wrapper') 
    carts.forEach(item => { 
        let cartItems = document.createElement('div')
        cartItems.classList.add('cart-items','gap10')
        let items = `<div class="cart-image mr-1">
                <img class="img" src="/${item.product.imagePath}" alt="" srcset="">
                <div class="cart-image-overlay flex-vert-center">
                    <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="cart-decription">
                <p class="cart-item-name">${item.product.name}</p>  
                <span class="cart-item-info">${item.qty}  x  ${item.price}</span>  
                <ul class="cart-item-variant">    
                </ul> 
            </div>`  
        
        cartItems.innerHTML = items
        let ul = cartItems.querySelector('.cart-item-variant')
        if(item.properties){
            item.properties.forEach(element => {
                ul.innerHTML += `<li><span class="capitalize">${element.name}: ${element.value}</span></li>`             
            });
        }   
        wrapperElement.appendChild(cartItems);
    });  
    return wrapperElement        
}
 
//  function cartsToViews(){  
//     let items = ""     
//     let div  = document.createElement('div');
//     let row = [];
//     carts.forEach(item => {         
//         items = `<div class="cart-items">
//             <div class="cart-image">
//                 <img class="img" src="/${item.product.imagePath}" alt="" srcset="">
//                 <div class="cart-image-overlay flex-vert-center">
//                     <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
//                 </div>
//             </div>
//             <div class="cart-decription">
//                 <p class="cart-item-name">${item.product.name}</p>  
//                 <span class="cart-item-info">${item.qty}  x  ${item.price}</span>  
//                 <ul class="cart-item-variant">    
//                 </ul> 
//             </div> 
//         </div>`
       
//         div.innerHTML =  items
       
//         const ul= div.querySelector('.sidebar-body')
//         items.properties.forEach(elem => {
//            `<li><span class="capitalized">${elem.name} : ${elem.value}</span></li>`          
//         })

//         row.push(div)
//     });  

//     console.log(row)
//     return items                
// }           



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
     sidecartTotal.innerHTML = `TOTAL: ${total}`

     let id = this.getAttribute("data-modal-target")
     openModal(id)
 });

