import { cartCountToElement, getCartCount } from "../module/Cart";
import { arrContains, arrFindIndex } from "../../module/array";
import { showMessage } from "../../module/alert"

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const cartBtn = document.getElementById('cart-button');
const BuyNowBtn = document.getElementById('buy-now');
const variantsOptions = document.querySelectorAll('.variant-options')

document.addEventListener('DOMContentLoaded', function(){
    checkHasVariants();     
})
              
function selectVaraints(elem){   
    let name = elem.getAttribute('name')
    let value = elem.getAttribute('value')
    populateProperties(name, value);   
    varaintSelected(elem)
 
}

variantsOptions.forEach(variant => {
    variant.onclick =  function(){
        selectVaraints(variant);
    }
})

// method check if hasVariants
function checkHasVariants(){ 
    if (cartBtn) {
        let id = cartBtn.getAttribute('data-id')
        getVariants(id)
    }
   
}

function varaintSelected(option){
    const ul = option.closest('ul');
    let lists = ul.querySelectorAll('.variant-options')

    lists.forEach(li => {
            li.classList.remove('selected');
     })
    option.classList.add('selected');


}

let properties = []
let hasVariants = false;

function populateProperties(name, value){
    let  exist = arrContains(properties, 'name' , name)
    if (exist) {
        let index =  arrFindIndex(properties, 'name', name)
        properties[index].value = value                
        return
    }            
    properties.push({ name : name , value : value }) 
}


function getVariants(id){
    $.ajax({
        url : '/product/hasvariant/'+id,
        type : 'GET',
        data : {          
            id : id,         
        },    
        success : function(response){      
                hasVariants = response.hasvariant;
                response.attributes.forEach(item => {
                    populateProperties(item,"");
                });            
        }
    })


}


function validateProperties()
{
    let result = []  
    let message = ""
    if(hasVariants){
       for (let i = 0; i < properties.length; i++) {
            if(properties[i].value == ""){
                message = "Please select " + properties[i].name
                result.push({status : true, message : message})
                return result; 
            }          
        }
    }
    result.push({status : false, message : message})
    return  result;
}

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
        success : function(res){
            cartCountToElement(res.count);
        },
        error: function(res){        
            console.log(res);    
        }
    })
}




//cart button event
if (cartBtn) {
    cartBtn.addEventListener('click',function(){
        let result =  validateProperties()
        if(result[0].status === true) return showMessage('info', result['0'].message) 
        store(); 
       
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









         

