
function toActionHead() {                            
    table.querySelector(".tr-action").classList.replace('hidden', 'show') 
}

function toDefaulthead(){              
    table.querySelector(".tr-action").classList.replace('show', 'hidden') 
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
    checkboxChildState(true)
    if( checkedCount() === 0) return
    toActionHead() 
    selectedItemcount.innerHTML =  checkedCount();
    return
}
checkboxChildState(false)
toDefaulthead();   
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