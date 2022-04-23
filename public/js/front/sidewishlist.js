let wishlists = []
let wishlistsTotal = 0

 function getwishlists(){                         
     $.ajax({
         url : '/api/wishlists',
         method : 'GET',
         async: false,
         success :  function(response){
             wishlists = response.wishlists  
             wishlistsTotal = response.total                  
         }
     });
 }

 function deleteWishlist(id){
     $.ajax({
         url : `/api/wishlists/delete/${id}'`,
         method : 'POST',
         data  : {
             _token : _token,
             _method : _delete
         },                  
         success :  function(response){               
         }
     });
 }

 
 function wishListToViews(){  
     let items = ""     
 
     wishlists.forEach(item => {    
         items += `<div class="cart-items">
             <div class="cart-image">
                 <img class="img" src="/${item.product.imagePath}" alt="" srcset="">
                 <div class="cart-image-overlay flex-vert-center">
                     <span onclick="wishlistRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
                 </div>
             </div>
             <div class="cart-decription">
                 <p class="cart-item-name">${item.product.name}</p>  
                 <span class="cart-item-info">${item.qty}  x  ${item.product.price}</span>  
                 <ul class="cart-item-variant">    
                 </ul> 
             </div> 
         </div>`        
     });  

  
     return items                
 }

  const sideWislist = document.querySelector('.wishlist-body')
  const sidewishlistTotal = document.querySelector('.wishlist-total')

// function clearCartElement()
// {
//      let wrapper = sidecart.querySelector('.cart-items-wrapper');
//      if(wrapper) return wrapper.remove();
   
// }

function wishlistRemove(elem){
     let id = elem.getAttribute('data-id')
     let parent = elem.closest('.cart-items');
     parent.remove();
     deleteWishlist(id)
}

 const wishlistModalTrigger = document.getElementById("sideWislist");  

 wishlistModalTrigger.addEventListener('click', function(){
    getwishlists();
    sideWislist.innerHTML = wishListToViews()
    sidewishlistTotal.innerHTML = `TOTAL: ${wishlistsTotal}`
     let id = this.getAttribute("data-modal-target")
     openModal(id)
 });

