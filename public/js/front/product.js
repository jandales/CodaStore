


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
        success : getCartCount,
        error: function(res){        
            console.log(res);    
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



const btnAddMinusQty = document.querySelectorAll('.btn-add-minus');

if (btnAddMinusQty) {
    btnAddMinusQty.forEach(button => {
        button.onclick = function(){
            const type = button.getAttribute('type');
            const input = document.getElementById('quantity-input');
            let qty = parseInt(input.value);
    
            if (type == 'add')  return input.value =  qty += 1;
    
            if (qty == 1) return  input.value =  1;
            input.value = qty -= 1;
        }
    })    
}









         

