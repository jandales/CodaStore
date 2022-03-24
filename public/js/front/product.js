


const cartBtn = document.getElementById('cart-button');
const BuyNowBtn = document.getElementById('buy-now');
const variantsOptions = document.querySelectorAll('.variant-options')

var url = "";

document.addEventListener('DOMContentLoaded', function(){
    checkHasVariants();  
})
              
function selectVaraints(elem){   
    let name = elem.getAttribute('name')
    let value = elem.getAttribute('value')
    populateProperties(name, value);   
    varaintSelected(elem)
}


// method check if hasVariants
function checkHasVariants(){ 
    let id = cartBtn.getAttribute('data-id')
    getVariants(id)
}

function varaintSelected(option){
    const ul = option.closest('ul');
    let lists = ul.querySelectorAll('.variant-options')

    lists.forEach(li => {
            li.classList.remove('selected');
     })
    option.classList.add('selected');


}


//cart button event
cartBtn.addEventListener('click',function(){
    let result =  validateProperties()
    if(result[0].status === true) return showMessage('info', result['0'].message) 
    store();
})


//method ajax request store
function store(){   
    let qty = document.querySelector('input[name="qty"]').value
    let url = cartBtn.getAttribute('url')    
    $.ajax({
        url : url,
        type : 'POST',
        data : {
            _token :  _token,
            qty : qty,
            properties : properties
        },       
        success : function(response){
            if(response.status == 501) return  showMessage('info', response.message)              
            
            if(response.status == 500){
                showMessage('success', response.message);
                cartWishlistCountToElement('.wishlist-count', getWislistCount())
            }
        },
        error: function(res)
        {
            return;
            console.log(res.status == 401);
            location.href = '/login';
        }
    })
}

if(BuyNowBtn){
    BuyNowBtn.addEventListener('click', function(e){
        e.preventDefault();   
        let inputProperties = document.querySelector('.properties')
        inputProperties.value = JSON.stringify(properties)
        let form =  document.getElementById("formCheckout") 
        let result =  validateProperties()
        if(result[0].status === true){
            showMessage('info', result['0'].message);
            return;
        }     
        form.submit();     
     });
}




function quantity(action)
{ 
    switch(action)
    {
        case 'minus' :                        
            minus();
        break;

        case 'add' :
            add();
        break;
    }
}

function minus()
{
    const input = document.querySelector('.quantity-input');
    const input1 = document.querySelector('.quantity');
    
    let qty = parseInt(input.value);
    if(qty == 0) return; 
    qty -= 1;
    input.value =  qty;
    input1.value = qty;
            
}

function add()
{
    const input = document.querySelector('.quantity-input');
    const input1 = document.querySelector('.quantity');
    
    let qty = parseInt(input.value); 
    qty += 1;
    input.value =  qty;
    input1.value = qty;
}



         

