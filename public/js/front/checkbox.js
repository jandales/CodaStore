// get parent checkbox element
const  parentCheckBox = document.getElementById('parentCheckbox')
// get childCheckbox element
const childCheckbox =  document.querySelectorAll('.childCheckbox')
// check if parent element exist
if(parentCheckBox)
{
    // create eventListener
    parentCheckBox.addEventListener('change', function(){
        // iterate child checkbox
        childCheckbox.forEach(elem => {
        
            if(!elem.hasAttribute('disabled'))
            {
                elem.checked = this.checked;
                rowfucos(elem, this.checked)
              
            }
        })
        
    })
}

if(childCheckbox){
    childCheckbox.forEach(checkbox => {
        checkbox.addEventListener('change', function(){
            rowfucos(checkbox, checkbox.checked)         
            checked()
        })
    })
}




function checked(){
    let childCount = childCheckbox.length
    let checkedfalse = 0
    let checkedtrue = 0

    childCheckbox.forEach(child => {
        if( child.checked ){
            checkedtrue += 1
        }
        else{
            checkedfalse += 1 
        }
    })

    if( checkedtrue == childCount ){
        parentCheckBox.checked = true            
    }
    if( checkedfalse == childCount){
        parentCheckBox.checked = false            
    }

}

function rowfucos(elem,bool = true){

    let tr = elem.closest('tr') 

    if(bool){    
        tr.classList.add('selected');        
    }
    else{
        tr.classList.remove('selected');        
    }
}
