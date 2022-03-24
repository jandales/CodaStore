
function remove(cart){

    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
 
    $.ajax({                 
            url: `/cart/destroy/${cart}`,
            method: 'post',
            dataType:"json",
            data : { 
                _token : token,
                _method : 'delete'
                },
            success:function(response){ 
                if(response.status == 200)
                {
                    location.reload()
                }                        
                  
            }
    })
}

function removeAll()
{
    let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');  
    
    if(getSelectedProduct().length == 0) return showMessage('danger', "Please select item to delete");
        


    $.ajax({
        url : `/cart/destroy`,
        method : 'post',
        dataType : 'json',
        data : {
            _token : token,
            _method :'delete',
            products : getSelectedProduct(),
        },
        success:function(response){
           
            if(response.status == 200)
            {
                location.reload()
            }  
        },
    })        
 
}

function getSelectedProduct()
{
    let products = [];
    let items = document.querySelectorAll('.childCheckbox');
    items.forEach(item => {
        if(item.checked) products.push(item.value)
    })   
    return products;
}

const parentCheckbox = document.getElementById('parentCheckbox');
const table = document.querySelector('table')
const childCheckbox =  document.querySelectorAll('.childCheckbox')
let selectedItemcount = table.querySelector(".item-selected")
let cancelAction = table.querySelector(".cancel")



function toActionHead() {                            
        table.querySelector(".tr-action").classList.replace('hidden', 'show')               
        table.querySelector(".tr-default").classList.replace('show', 'hidden')                   
}

function toDefaulthead(){              
        table.querySelector(".tr-action").classList.replace('show', 'hidden')               
        table.querySelector(".tr-default").classList.replace('hidden', 'show')  
                
}

function checkboxChildState(flag) {               
    childCheckbox.forEach(element => {                                            
            element.checked = flag 
            rowfucos(element)
    });
}

function checkedCount(){
    count = 0 
    childCheckbox.forEach(element => {
        if(element.checked){
            count += 1;
        }
    });                
    return count;                
}


cancelAction.addEventListener('click', function(){
    toDefaulthead();
    parentCheckbox.checked = false
    checkboxChildState(false)
})  


parentCheckbox.addEventListener('change', function(){            
    if (this.checked){
        toActionHead()                    
        checkboxChildState(true)
        selectedItemcount.innerHTML =  checkedCount(); 
        return
    }
    checkboxChildState(false)      
    selectedItemcount.innerHTML =  checkedCount();            
})

childCheckbox.forEach(elem => {               
    elem.addEventListener('change', function(){ 
        if(!elem.hasAttribute('disabled')){           
            rowfucos(elem) 
            parentCheckboxState()           
            if(checkedCount() > 1 || childCheckbox.length == 1){
                selectedItemcount.innerHTML =  checkedCount();  
                toActionHead();
                return
            }               
            toDefaulthead()
        }  
    }) 
})

function parentCheckboxState(){
    if(childCheckbox.length == checkedCount()) return parentCheckbox.checked = true                
    parentCheckbox.checked = false
}

function rowfucos(elem){
    let tr = elem.closest('tr') 
    if(elem.checked){    
        tr.classList.add('selected'); 
        return       
    }
    tr.classList.remove('selected'); 
}




















