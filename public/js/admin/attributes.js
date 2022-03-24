const btnattributeCreate = document.getElementById('btnFormCreate')
const btnattributeCancel = document.getElementById('btnFormCancel')
const btndelete = document.getElementById('btndelete')
const btnaddVariant = document.getElementById('btnaddVariant')
let attributes = []
let properties = {
    id : '',
    name : '',
    description : '',
    slug : ''
}
function getAttributes(){    
    $.ajax({
        url : '/admin/attributes/all',
        type : 'GET',
        async : false,
        success : function(response){
            attributes = response.attributes
        }
    })
}
function store(){
    fillProperites();
   
    if(properties.name == "") return alert("Please enter attribute name")
    $.ajax({
        url : '/admin/attributes/store',
        type : 'POST',
        data : {
            _token : _token,
            name : properties.name,
            description : properties.description,
            slug : properties.slug
        },
        async : false,
        success : function(response){
            if(response.status == "error") return alert(response.message)
            if(response.status == "success"){
                alert(response.message)
                getAttributes()
                attributesToView()
                clearForm()
            }

            
                
        }
    })
}
function fillProperites(){
    properties['name'] = document.querySelector('input[name="name"]').value
    properties['description'] = document.querySelector('input[name="description"]').value
    properties['slug'] = document.querySelector('input[name="slug"]').value
}
function fillInputForm(){
    document.querySelector('input[name="name"]').value =  properties.name
    document.querySelector('input[name="description"]').value = properties.description
    document.querySelector('input[name="slug"]').value = properties.slug
}
function populateVariants(variants)
{
    let html = ""
    variants.forEach((variant,index) => {           
        if(index == variants.length - 1) return html += `<li>${variant.value}</li>`
        html  += `<li>${variant.value}, </li>`       
    })
    return html
}
function attributesToView(){
    let table = document.querySelector('.table-body');
    table.innerHTML = "";
 
    attributes.forEach(item => {

        let description = item.description == null ? "----" : item.description 
        table.innerHTML  += `
        <tr>
            <td><div class="checkbox"><input type="checkbox" onclick="selected()" class="childCheckbox" name="selected[]"  value="${item.id}"> </div></td>
            <td><a class="item-name">${item.name}</a></td>
            <td>${description}</td>                   
            <td>
                <div class="table-action">
                    <ul>   
                        <li><a href="#"onclick="edit(${item.id})"><span class="span"><i class="fas fa-pen"></i> </span></a></li>
                        <li><a href="#" onclick="remove(this,${item.id})"><span class="span"><i class="fas fa-trash"></i></span> </a></li>   
                    </ul>
                </div>
            </td>
       </tr>`
     
    })
   
}
function destroy(id){
    let result = null
    $.ajax({
        url : `/admin/attributes/${id}/destroy`,
        type : 'POST',
        data : {
            _token : _token,
            _method : _delete,            
        },
        async : false,
        success : function(response){
            result = response
        }
    })
    return result;
}
function remove(elem, id){
    let result = destroy(id)
    if(result.status == 'success'){
        alert(result.message)
        elem.closest('tr').remove();
        getAttributes();        
    }
}
function formTitle(title,type,status){
    document.querySelector('.title').innerText = title
    btnattributeCreate.setAttribute('type', type)
    if(status == 'show') return  btnattributeCancel.classList.replace('hidden', status)
    btnattributeCancel.classList.replace('show',status)   
}
function clearForm(){
    document.querySelectorAll('input').forEach(input => {
        input.value = null
    })
    attributes= []; 
   
}
function edit(id){   
    let result = requestGet(`/admin/attributes/${id}`)
    formTitle('Edit attribute', 'update', 'show')
    properties = {
        id : result.attributes.id,
        name : result.attributes.name,
        description : result.attributes.description,
        slug : result.attributes.slug
    }
    fillInputForm()
}
function update(){
    fillProperites();   
    $.ajax({
        url : `/admin/attributes/update/${properties.id}`,
        type : 'POST',
        data : {
            _token : _token,
            _method : _put,        
            name : properties.name,
            description : properties.description,
            slug : properties.slug,
        },
        async : false,
        success:function(response){
            if(response.status == 'success'){  
                alert(response.message)              
                getAttributes()
                attributesToView()
                formTitle('Add new category', 'create', 'hidden')
                clearForm()
              
            }
        }
    })
}
function selectedItems(){
    const childCheckbox = document.querySelectorAll('.childCheckbox')
    let result = {id : [], count : 0}
    childCheckbox.forEach(item => {
        if(item.checked){
            result['id'].push(item.value)
            result['count'] += 1
        } 
    })
    return result
}
document.addEventListener("DOMContentLoaded", () => { 
    getAttributes();
    attributesToView();  
})
btnattributeCreate.addEventListener('click', function(){
    let type = this.getAttribute('type')
    if(type == 'update') return update()
    store()
})
btndelete.addEventListener('click', function(){
    let result = selectedItems()
    result.id.forEach((id,index) => {
        let res = destroy(id)  
        if(index == result.count - 1){
            if(res.status == 'success'){
                alert(res.message)
                getAttributes();
                attributesToView();
            }
        }           
    })   
})
btnattributeCancel.addEventListener('click', function(){
    formTitle("Add new attributes", 'create', 'hidden')
    clearForm()
})


// variants method

const inputVariantELement = document.getElementById('variant-name')
let variantListElement = document.querySelector('.variants-list-wrapper')

function createVariantHTML(variant){
    return `<div class="variant">
                ${variant.value}
                <span onclick="deleteVaraintEvent(this, ${variant.id})"><i class="fas fa-times"></i></span>
            </div>`
}
function variantsToHTML(variants){
    variantListElement.innerHTML = ""
    variants.forEach(variant => {
        variantListElement.innerHTML += createVariantHTML(variant)
    })
}
function getAttributeVaraints(){
    let result = null
    $.ajax({
        url : `/admin/attributes/variants/${properties['id']}`,
        type : 'GET',
        async : false,
        success: function(response){
            result =  response;
        }
    })
    return result
}
function addVariant(id,elementID){
    properties['id'] = id
    let result = getAttributeVaraints()
    let title = `Add variant ${result.attribute.name}`
    document.querySelector('.h4-title').innerText = title
    variantsToHTML(result.variants); 
    openModal(elementID)
}
inputVariantELement.addEventListener('keydown', event => {
    if (event.key === "Enter") {        
        let res = storeVariant(event.target.value)
        if(res.status == "error") return alert(res.message)        
        variantListElement.innerHTML += createVariantHTML(res.variant)
        getAttributes()
        attributesToView()
        event.target.value = ""                
    }  
})
function storeVariant(name){
    let res = null
    $.ajax({
        url : '/admin/variants/store',
        type : 'POST',
        data : {
            _token : _token,
            attribute_id : properties.id,
            name : name
        },
        async : false,
        success :function(response){
            res = response;
             
        }
    })
    return res
}
function deleteVariant(id){
    let result = null
    $.ajax({
        url : `/admin/variants/destroy/${id}`,
        type : 'POST',
        data : {
            _token : _token,
            _method : _delete,            
        },
        async : false,
        success: function(res) {
            result = res
        }

    })

    return result
}
function deleteVaraintEvent(elem,id){
    let res = deleteVariant(id);
    if(res.status == "success"){
        getAttributes()
        attributesToView()
        elem.parentElement.remove() 
    }  
}
