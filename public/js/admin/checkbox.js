// get parent checkbox element
const  parentCheckBox = document.getElementById('parentCheckbox')
// get parent checkbox element
let childCheckbox =  document.querySelectorAll('.childCheckbox')

//properties
let checkBoxProperties = { 
    background : "#EDF9FF",
    fucos : "rowfucos",     
    color : "#000" , 
    toolbar : true, 
    defaultToolbar : ".default-toolbar",
    actionToolbar : ".action-toolbar", 
    selecteCount : 1,   
}
//event
parentCheckBox.addEventListener('change', function(){
    childCheckbox =  document.querySelectorAll('.childCheckbox')
    childCheckboxState(this.checked) 
    if(!this.checked) return showToolbar()
    showToolbar(true)
    countChecked()    
})

childCheckbox.forEach(checkbox => {  
    checkbox.addEventListener('change', function(){     
       checkboxEvent(checkbox)
    })
 })

 //methods
function childCheckboxState(value){
    childCheckbox.forEach(elem => {                
        elem.checked = value
        rowFucos(elem)      
    })
}
function countChecked(){   
    let count = 0
    childCheckbox.forEach(elem => {
        if(elem.checked) count += 1
    })
    let selectedElement = document.querySelector('.selected-items')
    selectedElement.innerHTML = count + " Selected Items"
    return count;
}
function rowFucos(elem) {   
    let tr  = elem.closest('tr')   
    if(elem.checked) return tr.classList.add(`${checkBoxProperties.fucos}`); 
    tr.classList.remove(`${checkBoxProperties.fucos}`);   
}
function showToolbar(state = false){   
    const actiontoolbar = document.querySelector(`${checkBoxProperties.actionToolbar}`)
    const defaulttoolbar = document.querySelector(`${checkBoxProperties.defaultToolbar}`)   
  
    if(!checkBoxProperties.toolbar) return;
    if(state){        
        actiontoolbar.classList.remove('hidden')
        defaulttoolbar.classList.add('hidden')
        return;
    }  
    actiontoolbar.classList.add('hidden')
    defaulttoolbar.classList.remove('hidden')   
}

// reset all function
// clear checkbox selection
function clearSelection(){
    showToolbar()   
    parentCheckBox.checked = false  
    childCheckboxState(false)
}

function checkboxEvent(elem){
    rowFucos(elem)
    if(countChecked() == childCheckbox.length) parentCheckBox.checked = true
    if(countChecked() > checkBoxProperties.selecteCount) return showToolbar(true)    
    showToolbar(false)
    parentCheckBox.checked = false
}

function selected(){   
    childCheckbox =  document.querySelectorAll('.childCheckbox')
    childCheckbox.forEach(checkbox => {  
        checkbox.addEventListener('change', function(){     
           checkboxEvent(checkbox)
        })
    })
}


