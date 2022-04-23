
let Carts = [];
let couponProduct = [];
let coupon = {
    properties : [],
    products : [],  
}

const btnCoupon = document.getElementById('btnCoupon');
const couponInput = document.getElementById('input_coupon');

document.addEventListener('DOMContentLoaded', function(){
    // load when page load
    // get shipping fee
    let shippingfee = document.getElementById('shippingfee').getAttribute('data-fee');
    // get subtotal amout
    let subtotal = document.getElementById('subtotal').getAttribute('data-subtotal');
    // get total element
    let total = document.getElementById('total');
    // create a holder varialble for total
    let tempTotal = 0;
    // set total to tempTotal
    tempTotal = parseInt(shippingfee) + parseInt(subtotal);
    // show total in DOM
    total.innerText = tempTotal; 

    hasCouponActive();   

})

// check if coupon activate()
function hasCouponActive()
{
    $.ajax({
        url : '/user/active/coupon',
        method : 'GET',
        success : function(response){

            if(response.status == 400) return;

            if(response.status == "error")
            {
                 showMessage('danger',response.message);
                 return
            }           

             coupon.properties = response.coupon;
             coupon.products = response.products;  
          
             UseCoupon()

             setCouponRemove()

            
        }
    })
   
   
}

btnCoupon.addEventListener('click', function(e){

    e.preventDefault();    
    let remove = btnCoupon.getAttribute('remove');
     
    if(remove === 'true') {  
        removeCoupon();
        return;
    }
  
    applyCoupon();

})



function applyCoupon(){

    const value = couponInput.value;

    if(value == "") return;   

    if(coupon == "" | null) return showMessage('danger', 'Please input coupon');

    $.ajax({
        url: `/coupon/${value}`,
        method: 'get',       
        success:function(response){           
       
            if(response.status == "error")
            {
                showMessage("danger", response.message);   
                return;
            }

            coupon.properties = response.coupon;
            coupon.products = response.products;             
         
            UseCoupon()
            setCouponRemove();
            location.reload();
            
        }
    })
}

function removeCoupon(){   
    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
        url: `/coupon/user/${coupon.properties.id}/remove/`,
        method: 'put',
        data : {
            _token : token,
            _method : 'put',
         
        },      
        success:function(response){
            if(response.status == 500){
                location.reload()  
            }    
            
          
         }
    });
}

function setCouponRemove()
{
    btnCoupon.setAttribute('remove', true)
    btnCoupon.classList.replace('btn-default','btn-danger')
    btnCoupon.textContent = "REMOVE COUPON";
}

function setCouponApply(){
    btnCoupon.setAttribute('remove', false);
    btnCoupon.classList.replace('btn-danger', 'btn-default');
    btnCoupon.textContent = "APPLY COUPON";
}

function UseCoupon(){

    let discountType  = parseInt(coupon.properties.discount_type);
    let amount = parseInt(coupon.properties.amount);
  
    let shippingfee = parseInt(document.getElementById('shippingfee').getAttribute('data-fee'));
    // get subtotal amout
    let subtotal = parseInt(document.getElementById('subtotal').getAttribute('data-subtotal'));
    // get total element
    let total = document.getElementById('total');
    // create a holder varialble for total
    let tempTotal = 0;

    couponInput.value = coupon.properties.name;


    switch(discountType)
    {
        case 0 :
            updateCartProductsDiscount();           
            break;
        case 1 :
           
            tempTotal = (shippingfee + subtotal) - amount;
        // show total in DOM 
            total.innerText = tempTotal;  
                 
        break;

        case 2 :
            amount = amount / 100;            
            tempSubtotal = shippingfee + subtotal;
            tempTotal = tempSubtotal * amount;
            tempTotal -=  tempTotal;
              // show total in DOM    
            total.innerText = tempTotal; 
        break;                    
    }

   

   

}

function updateCartProductsDiscount(){

    const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({                
        url : '/cart/product/discount/update',
        method : "POST",
        data : {  
            products : coupon.products,  
            amount : coupon.properties.amount,       
            _token : token,
            _method : 'put',
        },
        success:function(response){}
    });

}