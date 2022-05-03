export function cartCountToElement(count)
{
    let elem = document.querySelector('.cart-count')
    if(count == 0) return elem.parentElement.classList.add('hidden')  
    elem.parentElement.classList.remove('hidden')
    elem.innerText = count;
}

export function cartCount(){
    let result = 0;
    $.ajax({
        url : '/cart/count',
        type: 'GET',
        async: false,  
        success : function(response){   
            result =  parseInt(response.cartItemsCount);         
        }
    });
    return result;

}

export function updateCartDOMELement(){
    cartCountToElement(cartCount);
}





export function setCartCookie()
{
    $.ajax({
        url : '/create-cart-cookie',
        type : 'GET',
        async : false,
        success : function(response){

        } 
    });
}
