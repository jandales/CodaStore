import { progressBarStart, progressBarStop} from '../module/progressbar'
import { arrContains, arrFindIndex, arrRemove} from '../module/array'
import { errorMessage, successMessage } from '../module/message'

let imagesToUpload = []
let errors = []
const product = {
    name : '',
    categories : '',
    shortDescription : '',
    longDescroption : '',
    sku : '',
    barcode : '',
    quantity : 0,
    sale_price : 0,
    regular_price : 0,
    isTaxable : false,
    tags : '',
    image : {},
    attributes : [],   
    images : [],
    status : ''    
}
let variants = [];
let attributes = [];
let images = [];

const optionsContainer = document.querySelector('.options-container')
const optionsWrapper = document.querySelector('.options-wrapper')
const hasVariantElement = document.querySelector('.has-variant')
const btnaddvariant = document.getElementById('btn-add-variant')
const selectAttributes = document.getElementById('selectInput')
const btnsave = document.getElementById('btnsave')


const btnfilter = document.getElementById('filter');

if(btnfilter) {
    btnfilter.onchange = function() {
        const url = btnfilter.options[btnfilter.selectedIndex].getAttribute('data-url');
        if(!url) return;   
        window.location.href = url;
    }
}




document.addEventListener("DOMContentLoaded", () => { 
        if(hasVariantElement){
            if(hasVariantElement.checked){
                getAttributes(); 
                optionsContainer.style.display = 'block'         
            } 
        }
        loadGalleryImages();
        loadEditOnEditForm();
})

function loadEditOnEditForm(){
    const inputVariant = document.querySelector('input[name="variants"]');
    const inputImage = document.querySelector('input[name="photos"]');
    const inputAttribute = document.querySelector('input[name="attributes"]');
    if(inputVariant) variants =JSON.parse(inputVariant.value);
    if(inputImage) images = JSON.parse(inputImage.value);    
    if(inputAttribute) attributes = JSON.parse(inputAttribute.value);
    
    attributes.forEach(attribute => {      
        product.attributes.push({ id : attribute.attribute_id, variants : [] })  
    });

    product.attributes.forEach(item => {      
        variants.forEach(variant => {            
            if(item.id == variant.attribute_id) item.variants.push(variant.variant)
        })           
    })  

    images.forEach(image => {
        product.images.push({id : image.id, path : image.path, deleted : 0 })
    });

}
function getAttributes(){ 
    let res = null;
    $.ajax({
        url : '/admin/attributes/all',
        type : 'GET',
        async : false,
        success : function(response){
            res = response.attributes  
            selectAttributes.innerHTML = ''
            response.attributes.forEach(attribute => {               
                selectAttributes.innerHTML += `<option value = ${attribute.id}>${attribute.name}</option>`
            })
        }
    })
    return res
}
function createOption(){
    const text = selectAttributes.options[selectAttributes.selectedIndex].text
    const value = selectAttributes.value
    const html = `<div class="options">                       
                    <div class="options-selector m-t-1" >
                        <div class="option-attribute">
                            ${text}
                        </div> 
                        <div class="variants-wrapper">
                            <div class="variants-list-wrapper">                                   
                            </div>                
                            <input data-id ="${value}"  class="inputVariant no-border"  placeholder="Enter varaint name and hit enter" type="text" name="variant_name[]" value=""> 
                        </div>                                 
                    </div>
                    <span class="option-remove" id="${value}">remove</span>
                </div>`
    
    return elementFromHtml(html);
}

function elementFromHtml(html) {
    const template  =  document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstElementChild;
}


function createVariantHTML(name, id){
    return `<div class="variant">  
                ${name}          
                <span name="${name}" data-id="${id}" class="remove-variant-item"><i class="fas fa-times"></i></span>
            </div>`
}
function addVariantItem(id,value){   
    let i = arrFindIndex(product.attributes,'id',id)    
    product.attributes[i].variants.push(value)  
}
function deleteVariantItem(id,value){  
    let i = arrFindIndex(product.attributes,'id',id) 
    arrRemove(product.attributes[i].variants, value)
}
function existVariantItem(id,value){ 
    let i = arrFindIndex(product.attributes,'id',id)   
    return arrContains(product.attributes[i].variants, value)
}
function storeProduct(){ 
   errors = []
   errorMessage([]) 
   const form = document.getElementById('form')
   const url = form.getAttribute('action')
   const data =  new FormData(form)
   product.images.forEach(image => {      
        data.append('images[]', JSON.stringify(image))
   })
   product.attributes.forEach(attribute => {
        data.append('attributes[]', JSON.stringify(attribute))
   })
   data.append('image',JSON.stringify(product.image))

     $.ajax({
        url : url,
        type : 'POST',
        data : data,
        datatype:"json", 
        processData: false,
        contentType: false,
        cache: false,
        success:function(res){  
            if(res.status == 200) successMessage(res.message) 
        },
        error:function(XMLHttpRequest){
            let resErrors =  XMLHttpRequest.responseJSON.errors      
            if(XMLHttpRequest.status === 422){            
                for(const key in resErrors) errors.push({ name : key , message  : resErrors[key] })
                errorMessage(errors);
            }
        }
    })   
}
function updateProduct(){  
    errors = []
    errorMessage([])  
    const form = document.getElementById('form')
    const url = form.getAttribute('action')
    const data =  new FormData(form)
    product.images.forEach(image => {      
        data.append('images[]', JSON.stringify(image))
    })
    product.attributes.forEach(attribute => {
        data.append('attributes[]', JSON.stringify(attribute))
    })
    data.append('image',JSON.stringify(product.image))
    data.append('_method', 'PUT');
     $.ajax({
         url : url,
         type : 'POST',
         data : data,
         datatype:"json", 
         processData: false,
         contentType: false,
         cache: false,
         success:function(res){ 
             if(res.status == 200) successMessage(res.message) 
         },
         error:function(XMLHttpRequest){
             let resErrors =  XMLHttpRequest.responseJSON.errors      
             if(XMLHttpRequest.status === 422){            
                 for(const key in resErrors) errors.push({ name : key , message  : resErrors[key] })
                 errorMessage(errors);
             }             
         }
    })
}
// events //
if(hasVariantElement) {
    hasVariantElement.onchange =  function(){
        if(this.checked){
            getAttributes(); 
            optionsContainer.style.display = 'block'
            return
        } 
        const options = document.querySelectorAll('.options')
        optionsContainer.style.display = 'none'    
        product.attributes = []
        options.forEach(option => {
                option.remove();
        })   
    }
}






function removeVariantItem(){ 
    const removeVariantItems = document.querySelectorAll('.remove-variant-item');
    removeVariantItems.forEach(item => {
        item.onclick =  function(){
            console.log(item);
            let value  = item.getAttribute('name')
            let variant_id = parseInt(item.getAttribute('data-id'))     
            deleteVariantItem(variant_id,value)
            item.parentElement.remove();  
        }       
    })
  
}



if (btnaddvariant) {
    btnaddvariant.addEventListener('click', function(e) {
        e.preventDefault();
        let selected =  parseInt(selectAttributes.value)
        if(arrContains(product.attributes, 'id', selected)) return alert('varaint already selected')        
        product.attributes.push({ id : selected, variants : [] })    
        optionsWrapper.appendChild(createOption());
        variantInput();
        variantRemove();       
    });
}

/// add event 
function addVariantEvent(event){ 
    console.log(event);  
    if (event.keyCode === 13) {     
        event.preventDefault(); 
        let value = event.target.value
        let id = parseInt(event.target.getAttribute('data-id')) 
        if(value == "" ) return   
        if(existVariantItem(id,value)) return event.target.value = ""  
        addVariantItem(id,value)      
        
        let wrapper = event.target.parentElement.querySelector('.variants-list-wrapper')
        wrapper.innerHTML += createVariantHTML(value, id) 
        event.target.value = ""   
        removeVariantItem();        
    } 
}

function variantInput(){
    const inputVariants =  optionsWrapper.querySelectorAll('.inputVariant')      
    inputVariants.forEach(input => {
        input.addEventListener('keypress', function(event){   
            addVariantEvent(event)
        });
    })
}



function removeOption(e){  
    const elem = e.target;  
    let id = elem.getAttribute('id')
    arrRemove(product.attributes, 'id', id)   
    elem.parentElement.remove()
}

function variantRemove(){
    const removes =  optionsWrapper.querySelectorAll('.option-remove')      
    removes.forEach(remove => {
        remove.onclick =  removeOption;
    })
}
























/// file uploader
const fileProductImage  =  document.getElementById('file-product-image')
const fileImageGallery  =  document.getElementById('file-image-gallery')
const imagegallery = document.querySelector('.image-gallery')
let path = "";



function loadGalleryImages(){ 
    if(product.images.length == 0) return;
    imagegallery.innerHTML = ''   

    product.images.forEach(image => {
        if(image.deleted == 0){
            imagegallery.innerHTML += `
            <div class="image">
                <img src="/${image.path}" alt="">                                   
                <span data-id="${image.id}" class="remove remove-gallery-image"><i class="fas fa-times"></i></span>
            </div>
            `
        }       
    })    
    setRemoveGalleryImageEvent()
} 
function loadImage(image){    
    const imageElement = document.querySelector('.image-product .image')
    imageElement.innerHTML = `<img src="/${image.path}" alt=""><span class="remove remove-product-image"><i class="fas fa-times"></i></span>`
    SetRemoveProductImageEvent();
}

function setRemoveGalleryImageEvent(){ 
    const removeGalleryImageElement =  document.querySelectorAll('.remove-gallery-image');
    removeGalleryImageElement.forEach(elem => {
        elem.onclick =  function(){
            const id = parseInt(elem.getAttribute('data-id'));
            product.images.forEach(image => {                
                if (image.id == id) image.deleted = 1
            }) 
            elem.parentElement.remove() 
        }
    })
     
}
function SetRemoveProductImageEvent(){   
    const removeProductImageElements  = document.querySelectorAll('.remove-product-image');
    removeProductImageElements.forEach(elem => {
        elem.onclick = function(){
            product['image'] = {id : '', path : ''}  
            elem.parentElement.querySelector('img').remove()
            elem.remove() 
        }
    })
     
}
function deleteImage(id){
    let result = false
    $.ajax({
        url : `/admin/image/${id}/delete`,
        type : 'POST',
        data : { 
            _token : _token,
            _method : _delete
        },
        async : false,
        success: function(response) {
            result = response.deleted
        }
    })
    return result;
}

function uploads(element, multiple = true){  
    const form = document.getElementById('formUpload')
    var formData = new FormData(form);  
    for(let i in imagesToUpload){       
        formData.append('images[]', imagesToUpload[i])        
    } 
    $.ajax({
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(e) {             
                const percent = e.lengthComputable ? (e.loaded / e.total) * 100 : 0 
                progressBarStart(element,percent.toFixed(2)) 
           }, false);     
           return xhr;
        },
        url : `/admin/image/uploads`,
        type : 'post',
        data : formData,
        cache:false,
        contentType: false,
        processData: false,       
        success:function(res){
            progressBarStop(element)          
            imagesToUpload = [] 

            if(multiple){                             
                res.images.forEach(image => { product['images'].push(image) })
                loadGalleryImages()                
                return;
                
            }  

            loadImage(res.images[0])
            product.image = { id :  res.images[0].id, path : res.images[0].path }  
        },      
    })
}

if (fileProductImage) {
    fileProductImage.onchange =  function(e) {
        e.preventDefault();
        const progressWrapper = document.querySelector('.progress-image') 
        imagesToUpload.push(this.files[0])    
        uploads(progressWrapper,false)  
    }
}

if (fileImageGallery) {
    fileImageGallery.onchange =  function(){
        Array.from(this.files).forEach(image => {      
            imagesToUpload.push(image)      
        }) 
        const wrapper = document.querySelector(`.progress-images`)  
        imagegallery.style.minHeight = "200px"
        uploads(wrapper) 
    }
}

if (btnsave) {
    btnsave.onclick =  function(e){
        e.preventDefault();
        const type = this.getAttribute('type');
        if (type == 'create') return storeProduct();
        updateProduct(); 
    }
}








