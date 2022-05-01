

const btnCategoryDelete = document.getElementById('btn-selected-cateory-delete');
const btndeleteAttributes = document.getElementById('btn-delete-attribute');
if (btnCategoryDelete){
    
    btnCategoryDelete.onclick =  function(){

        let url = this.getAttribute('data-id');
        const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selectedId = [];
        document.querySelectorAll('.childCheckbox').forEach( element => {
            if(element.checked) {
                let id = element.value;
                selectedId.push(id);
            }
        
        })
        $.ajax({
            url : url,
            type : 'post',
            data : {
                _token : _token,
                _method : 'delete',
               selectedId : selectedId,
            },
            success : function(res){              
                window.location.href = res;
            }
        })
    }
}

if (btndeleteAttributes){
    
    btndeleteAttributes.onclick =  function(){

        let url = this.getAttribute('data-url');
        const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let selected = [];
        document.querySelectorAll('.childCheckbox').forEach( element => {
            if(element.checked) {
                let id = element.value;
                selected.push(id);
            }
        
        })
        $.ajax({
            url : url,
            type : 'post',
            data : {
                _token : _token,
                _method : 'delete',
               _selected : selected,
            },
            success : function(res){              
                window.location.href = res;
            }
        })
    }
}


const btnCloseSearch = document.querySelectorAll('.close-search');

btnCloseSearch.forEach(close => {
    close.onclick = function() {
        let url = close.getAttribute('data-url');
        window.location.href = url;
    }
})




const fileInput = document.getElementById('file-input');
const imageRemove = document.getElementById('remove-image')
const image = document.getElementById('image-to-upload');

if(fileInput){
    fileInput.onchange = function () {

        if (!fileInput.files && !fileInput.files[0]) return;                  
            
        var reader = new FileReader()
            
        reader.onload = function (e) { 
            image.setAttribute('src', e.target.result);
            image.classList.remove('hidden');
        }

        reader.readAsDataURL(fileInput.files[0]) 
        imageRemove.classList.remove('hidden');
}
}

if (imageRemove) {
    imageRemove.onclick = function(){
        image.setAttribute('src', null);
        fileInput.value = null;
        imageRemove.classList.add('hidden');
        image.classList.add('hidden');
    }
}







// let btncategoryStore = document.getElementById('btncategoryCreate');
// let btncategoryCancel= document.getElementById('btncategoryCancel')
// const inputSearch = document.querySelector('.txtsearch')
// const searchIconClose = document.querySelector('.search-close-icon')
// let listCategories =  []

// let categoryProperties = {
//     id : '',
//     name : '',
//     description : '',
//     slug : ''
// }

// document.addEventListener("DOMContentLoaded", () => { 
//     getCategories()
//     categoriesToView() 
// })
// function  fillCategoryProperties(){
//     categoryProperties['name'] = document.querySelector('input[name="name"]').value
//     categoryProperties['description'] = document.querySelector('input[name="description"]').value
//     categoryProperties['slug'] = document.querySelector('input[name="slug"]').value
// }
// function fillFieldInput(){
//     document.querySelector('input[name="name"]').value = categoryProperties['name']
//     document.querySelector('input[name="description"]').value = categoryProperties['description']
//     document.querySelector('input[name="slug"]').value = categoryProperties['slug']
// }
// function getCategories(){  
//     $.ajax({
//         url : `/api/admin/products/categories`,
//         type : 'GET',        
//         async : false,
//         success:function(response){
//             listCategories = response.categories;  
//         }
//     })    
// }
// function categoriesToView(){  
//     let view = ""
//     listCategories.forEach(category => {
//     let description = category.description == null ? "----" : category.description     
//     view += `<tr>
//                     <td>
//                         <div class="checkbox">
//                             <input type="checkbox" onclick="selected()" class="childCheckbox" name="selected[]"  value="${category.id}">
//                         </div>
//                     </td>  
//                     <td>${category.name}</td><td>${description}</td><td>${category.slug}</td>
//                     <td class="column-action">                            
//                         <div class="table-action">
//                                     <ul>   
//                                         <li>                                   
//                                             <a href="#" onclick="editCategory(${category.id})">
//                                                 <span class="span">
//                                                     <i class="fas fa-pen"></i>  
//                                                 </span>                                                                           
//                                             </a>
//                                         </li> 
//                                         <li> 
//                                             <a href="#" onclick="remove(this,${category.id})">
//                                                 <span class="span">
//                                                     <i class="fas fa-trash"></i>  
//                                                 </span>                                                                           
//                                             </a>               
//                                         </li>   
//                                     </ul>
//                         </div>
//                     </td>
//                 </tr>`
//         })


//     if(listCategories.length == 0 ) {
//         view = `<tr><td></td><td colspan="4" class="txt-center">No Categories Found</td><tr>`
//     }

//     let table = document.querySelector('.categories-body'); 
//     table.innerHTML = view;

// }
// function storeCategory(){
//     let result = null;
//     $.ajax({
//         url : '/admin/products/categories/store',
//         type : 'POST',
//         data : { 
//             _token : _token,
//             name : categoryProperties.name,
//             description : categoryProperties.description,
//             slug : categoryProperties.slug
//         },       
//         async : false,        
//         success:function(response){           
//               if(response.status == 500) return alertMessage(response.message) 
//               getCategories()
//               categoriesToView()
//               clearForm()
//         },
//         error:function(XMLHttpRequest){            
//             alertMessage(XMLHttpRequest.responseJSON.errors.name)
//          }
//     })

//     return result;
// }
// function  updateCategory(){
    
//     $.ajax({
//         url : `/admin/products/categories/update/${categoryProperties.id}`,
//         type : 'POST',
//         data : { 
//             _token : _token,
//             _method : _put,
//             name : categoryProperties.name,
//             description : categoryProperties.description,
//             slug : categoryProperties.slug
//         },
//         async : false,        
//         success : function(response){           
//             if(response.status == 200){
//                 getCategories()
//                 categoriesToView()
//                 clearForm()
//                 formTitle('Add new category','create','hidden')
//             }
//         }
//     })

// }

// function getCategoryById(id){
//     let result;
//     $.ajax({
//         url : `/admin/products/categories/${id}/edit`,
//         type : 'GET',        
//         async : false,
//         success:function(response){
//             result = response
//         }
//     })
//     return result;
// }
// function editCategory(id){ 
//     let  result = getCategoryById(id);
//     formTitle('Edit Category', 'update', 'show')    
//     categoryProperties = {
//         id : result.category.id,
//         name : result.category.name,
//         description : result.category.description,
//         slug : result.category.slug
//     }   
//     fillFieldInput();
// }
// function remove(e,id){   
//    let result = deleteCategory(id);   
//    if(result.status == 'success') {      
//         e.closest('tr').remove();
//    }
// }
// function clearForm(){
//     document.querySelectorAll('input').forEach(input => {
//         input.value = null
//     })

//     clearCategoriesProperties()
   
// }
// function clearCategoriesProperties(){
//     categoryProperties = { id : '', name : '', description : '',  slug : '' }
// }
// function formTitle(title,type, status){
//     document.querySelector('.title').innerText = title
//     btncategoryStore.setAttribute('type', type)
//     if(status == 'show'){
//         btncategoryCancel.classList.replace('hidden', status)
//         return;
//     }    
//     btncategoryCancel.classList.replace('show',status)
   
// }
// function deleteCategory(id){
//     let url = `/admin/products/categories/delete/${id}`
//     return requestDelete(url)
// }
// function deleteCategories(){
//     let checkboxs = document.querySelectorAll('.childCheckbox')
//     checkboxs.forEach(item => {
//         if(item.checked){
//             remove(item, item.value)                 
//         }
//     })
// }
// function closeSearch(){
//     inputSearch.value = ""
//     searchIconClose.classList.add('hidden')
//     getCategories()
//     categoriesToView()
// }

// inputSearch.addEventListener("keypress", function onEvent(event) {
//     if (event.key === "Enter") {
//        searchIconClose.classList.remove('hidden')
//        search(this.value)
//     }
// });


// function search(input){  
//     let url = '/admin/products/categories/search'
//     listCategories = requestSearch(url, input).categories
//     categoriesToView();
// }


// // event
// if (btncategoryStore){
//     btncategoryStore.addEventListener('click', function(){
//         let type = this.getAttribute('type')
//         fillCategoryProperties() 
//         if(type == 'update') return  updateCategory();
//         storeCategory();
//     })
// }
// if (btncategoryCancel){
//     btncategoryCancel.addEventListener('click', function() {
//         formTitle('Add new Category', 'create', 'hidden')
//         clearForm()
//     })
// }





