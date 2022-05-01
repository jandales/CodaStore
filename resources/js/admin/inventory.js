const btnAddMinusQty = document.querySelectorAll('.btn-add-minus');
const btnsave = document.querySelectorAll('.btn-save');
const numProduct = document.querySelectorAll('.num-product');
let grandParentElement;
const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _put = 'PUT';
if (btnAddMinusQty) {
    btnAddMinusQty.forEach(button => {
        button.onclick = () =>  {
            btnsaveEvent(button);
        }
    })    
}
function btnsaveEvent(button){
    const grandParent = button.closest('tr'); 
    const type = button.getAttribute('type');
    const input = button.parentElement.querySelector('.num-product');
    let qty = parseInt(input.value);              
            
    if (type == 'add') { 
        input.value =  qty += 1;  
    } 
    else {                                
        if (qty == 0) return  input.value =  0;                    
        input.value = qty -= 1;
    }

    const stock = parseInt(grandParent.querySelector('.stock').innerHTML);

    if (qty === 0)            
        buttonState(grandParent, true);
    else             
        buttonState(grandParent, false)   

    // updateStockElement(grandParent, qty)
}

function buttonState(element, state){ 
    const btn =  element.querySelector('.btn-save');    
    if (state == true)
        btn.setAttribute('disabled',state);
    else
        btn.removeAttribute('disabled');
}

function save(url, qty, action){
    $.ajax({
        url : url,
        type : 'POST',
        data : {
            _token : _token,
            _method : _put,
            action : action,
            qty : qty,
        },
        error : errorResponse,
        success : successResponse,
    })
}
function errorResponse(response){}
function successResponse(response){
    const currentstock = grandParentElement.querySelector('.stock');
    const numproduct = grandParentElement.querySelector('.num-product');
    currentstock.innerHTML = response.stock.qty;   
    numproduct.value = 0;
    buttonState(grandParentElement, true);
}
btnsave.forEach(btn => {
    btn.onclick = function(){
        const grandParent = btn.closest('tr');       
        const url = btn.getAttribute('data-url');
        const qty = parseInt(grandParent.querySelector('.num-product').value)
        const selectedType = grandParent.querySelector('.type');
        const type = selectedType.options[selectedType.selectedIndex].value;
        grandParentElement = grandParent;     
        save(url, qty, type);
    }
})

const btnfilter = document.getElementById('filter');
if(btnfilter){
    btnfilter.addEventListener('change', function() {
        const url = btnfilter.options[btnfilter.selectedIndex].getAttribute('data-url');
        if(!url) return;
        window.location.href = url;
    })
}




numProduct.forEach(input => {
    input.addEventListener('change', () => {
        let qty = parseInt(input.value);
        const grandParent = input.closest('tr'); 
        let state = false;

        if (qty <= 0) {
            input.value = 0;
            state = true;
        }

        buttonState(grandParent, state);           
        
    })
})