const inputs = document.querySelectorAll('.product_search');

inputs.forEach(input => {
    input.addEventListener('input', search)
});


function search(e) {

    let type = e.target.getAttribute('data-type');             
    let value = e.target.value

    const element = e.target.parentElement.parentElement.parentElement;
    
    const parent  = element.querySelector('.dropdown-ul-list')

    $.ajax({
        url : '/admin/product/searchByajax/' + value,
        method : 'GET',             
        success: function(response){                          
             emptylist(); 
             response.forEach(product => {
                            createList(parent,product,type); 
             }) 
        }
    })
}

inputs.forEach(input => {
    // set  addEventListener 
    input.addEventListener('input', openlist)      

});

function openlist(e)
{ 
    // get parent element
    const parent = e.target.parentNode.parentNode
    // get the list parent element
    const listParent = parent.querySelector('.dropdown-list-wrapper')
    // check if target is null
    if(e.target.value == null | e.target.value == "") 
    {
        // add class open in list parent element
        return  listParent.classList.remove('open')
    }
     // remove class open in list parent element
    listParent.classList.add('open')
}



let productList = [];

// function to get the set new array
function setProductList(array)
{
    productList = array;
}

function  getProductList(){
    return productList;
}






function createList(parent, data, type)
{
  
    let li = document.createElement('li')
    li.addEventListener('click', select);
    li.setAttribute('data-type',type)
    li.setAttribute('value-name', data.name);
    li.setAttribute('value-id', data.id )
    li.innerText = data.name
    parent.appendChild(li);
}

/// create product item element in DOM
function addproductListElement(parent, data = []){
    // create li element
    let li = document.createElement('li');               
    // append elem in li
    li.innerHTML = '<span>'+ data['name'] +'<span onclick="remove(this,' + `${data['id']}` + ')" class="close"><i class="fa fa-times"></i></span></span></li>';
   // get parent element
   const ul = parent.parentNode.parentNode.querySelector('ul');  
   // display the list in DOM
   ul.classList.add('show');  
   // append li to ul      
   ul.appendChild(li);               
}

function select(e)
{
   
    // get parent element of select item
    const parent = e.target.parentNode.parentNode;
    /// set data in variables 
    let data = {                   
          id : e.target.getAttribute('value-id'),
          name :  e.target.getAttribute('value-name'),
          type : e.target.getAttribute('data-type')   
    }     
    // check if data already exist in productlist 
    if(addproductList(productList,data))  return alert('Exclude list: product already exist')
    //create product elem in DOM
     addproductListElement(parent,data);              
    /// close the dropdown after the selection              
    parent.classList.remove('open')
    // clear input
    clearinput()

    
}        

function addproductList(array, data)
{ 
    // check if product already exist in array
    // contains method return boolen  
    if(arrContains(array, 'id' , data['id'])) return true;               
    /// push the product into  array and return false
    array.push({
        "id" : parseInt(data['id']),
        "name" : data['name'],
        "type" : data['type']
    })   
            
    return false; 
}

function remove(elem, value){
    /// remove item in array
    arrRemove(productList, 'id', value);            
    // get the parent element
    const parent = elem.parentNode.parentNode
    // get the  grandparent element 
    const grandParent = elem.parentNode.parentNode.parentNode
    // remove element in DOM                      
    grandParent.removeChild(parent);                 
    //if productlist is null
    Showlist()


} 





function clearinput()
{
    inputs.forEach(input => {
        input.value = ""               
    })
    emptylist();             
}





function emptylist(){   
     // get the product list <li> parent                              
     const parents = document.querySelectorAll('.dropdown-ul-list') 
     // for each li element remove from parent element
     parents.forEach(parent => {
         while (parent.firstChild) {
            parent.firstChild.remove()
        }  
     })                 
 }

 function Showlist(){

    const parents = document.querySelectorAll('.product-list')

    parents.forEach(parent => {
        
              let type = parent.getAttribute('data-type'); 
              
              if(arrContains(productList, 'type', type)){
                parent.classList.add('show')
                return true;
              }
             
    })
         
 }
 function populateList(array)
 {
    array.forEach(arr => {
        productList.push({
            "id" : arr.id,
            "name" : arr.name,
            "type" : arr.type
        })
    }); 
 }


 function populateElementDOM(){

    const parents = document.querySelectorAll('.product-list')


    productList.forEach(list => {                
                parents.forEach(parent => {
                    let type = parent.getAttribute('data-type'); 
                    if(list.type == type)
                    {
                        const li =  document.createElement('li');
                        li.innerHTML = `<span>${list.name}<span onclick="remove(this,${list.id})" class="close"><i class="fa fa-times"></i></span></span>`
                        parent.appendChild(li)
                    }                  

        })

    })


}


 const button = document.getElementById('save');

 if(button) { button.addEventListener('click', submit) }

 function submit()
 {  
    event.preventDefault();

    let form = document.getElementById('form');
    document.getElementById('productlist').value =  JSON.stringify(productList);
    form.submit();
   
 }
