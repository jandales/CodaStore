const  parentCheckBox = document.getElementById('parentCheckbox')
const childCheckbox =  document.querySelectorAll('.childCheckbox')

parentCheckBox.addEventListener('change', function(){

childCheckbox.forEach(elem => {

    if(!elem.hasAttribute('disabled'))
    {
        elem.checked = this.checked;
        rowfucos(elem)
      
    }
})

})

childCheckbox.forEach(checkbox => {
        checkbox.addEventListener('change', function(){
            rowfucos(checkbox)         
            checked()
        })
})

function checked(){
    let childCount = childCheckbox.length
    let checkedtrue = 0

    childCheckbox.forEach(child => {
        if( child.checked ){
            checkedtrue += 1
        }      
    })

    if( checkedtrue == childCount ){
        parentCheckBox.checked = true            
    }else if(checkedtrue == 0)
    {
        parentCheckBox.checked = false   
    }
  
    

}

function rowfucos(elem){

    let tr = elem.closest('tr') 

    if(elem.checked){    
        tr.classList.add('selected');
        return       
    }
   
    tr.classList.remove('selected');        
    
}
