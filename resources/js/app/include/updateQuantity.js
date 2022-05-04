const updateQuantity = document.querySelectorAll('.add-minus-quantity')

// get all quanity Element
const cartQtys = document.querySelectorAll('.cart-qty');


// iterate all quantity
cartQtys.forEach(element => {
    // create event for every element
    element.addEventListener('change', function() {
        // get data for each element
        const cart = element.getAttribute('item');
        // get quantity for each element
        let qty = element.value
        updateQty(cart,qty);

    })
})





updateQuantity.forEach(element => {
    element.addEventListener('click', function() {
        // get type (add, minus) 
        let type = element.getAttribute('type')
        // get quantity  element
        let input = element.closest('td').querySelector('.cart-qty')
        // get current item in the cart
        let cart = input.getAttribute('item');
        // create var qty
        let qty = 0;

        // if type is add
        if(type == 'add'){            
            qty=  parseInt(input.value) +  1   
            // call ajax request to update cart quantity
            updateQty(cart,qty);
            return;                          
        }
        // if type is minus
        if(type == 'minus'){
            qty=  parseInt(input.value) -  1 
             // call ajax request to update cart quantity 
            updateQty(cart,qty);
            return;
        }       
      
    });
})


// ajax call update quantity

function updateQty(cart, qty){

    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
       
        $.ajax({
            url: `/cart/update/${cart}`,
            method: 'post',
            data : { 
                _token : token,
                quantity : qty
                },
            success:function(response){
                console.log(response);
                if(response.status == "error") {
                   showMessage("danger", response.message); 
                   return;
                }                       
                return;
                // location.reload()
                  
            }
        })
}