// import { progressStart, progressStop, readFile ,} from '/js/admin/utilities.js'
let imagesToUpload = []
const product = {
    name : '',
    categories : '',
    shortDescription : '',
    longDescroption : '',
    sku : '',
    barcode : '',
    quantity : 0,
    price : 0,
    regular_price : 0,
    tags : '',
    image : {id: '', file : ''},   
    attributes : [],   
    images : [],
    status : ''
}

const optionsContainer = document.querySelector('.options-container')
const optionsWrapper = document.querySelector('.options-wrapper')
const hasHariantElement = document.querySelector('.has-variant')
const btnaddvariant = document.getElementById('btn-add-variant')
const selectAttributes = document.getElementById('selectInput')
const btnsave = document.getElementById('btnsave')


document.addEventListener("DOMContentLoaded", () => { 
    getCategories()
  
})
function getCategories(){
    $.ajax({
        url : '/api/admin/categories',
        type : 'GET',
        async : false,
        success : function(res){
            const select = document.getElementById('categories')
            select.innerHTML = ''
            res.categories.forEach(category => {
                select.innerHTML += `<option value = ${category.id}>${category.name}</option>`
            })
        }
    })  
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
                            <input data-id ="${value}" class="inputVariant no-border" onkeydown="addVariantEvent(event)" placeholder="Enter varaint name and hit enter" type="text" name="variant_name[]" value=""> 
                        </div>                                 
                    </div>
                    <span class="option-remove" id="${value}" onclick="removeOption(this)">remove</span>
                </div>`
    return html
}
function createVariantHTML(name, id){
    return `<div class="variant">  
                ${name}          
                <span name="${name}" data-id="${id}" onclick="removeVariantItem(this)"><i class="fas fa-times"></i></span>
            </div>`
}
function addVariantItem(id,value){   
    let i = arrFindIndex(product.attributes,'id',id)    
    product.attributes[i].variants.push(value)  
}
function deleteVariantItem(id,item){
    let i = arrFindIndex(product.attributes,'id',id) 
    arrRemove(product.attributes[i].items, item)
}
function existVariantItem(id,value){    
    let i = arrFindIndex(product.attributes,'id',id)   
    return arrContains(product.attributes[i].variants, value)
}
function fillproductproties(){    
    product.name = input('name').value
    product.categories = getElementById('categories').value    
    product.sku = input('sku').value
    product.barcode = input('barcode').value
    product.shortDescription = getElementById('short_description').value
    product.longDescroption = getElementById('long_description').value
    product.price = parseFloat(input("price").value)
    product.regular_price = parseFloat(input("regular_price").value)
    product.status = getElementById('status').value
    product.quantity = parseInt(input('qty').value)  
}
function save(){ 
    
    const form = document.getElementById('form')
    var formData = new FormData(form);
    formData.append('productImage',product.image) 
    for(const image in product.images){
        formData.append('images[]', image) 
    }
    // for(let i in imagesToUpload){ 
    // } 
    // {
    //     _token : _token,
    //     data : 
    //     name : product.name,
    //     categories : product.categories,                                   
    //     description : product.shortDescription,
    //     long_description : product.longDescroption,
    //     productImage : product.image,
    //     sku : product.sku,
    //     barcode : product.barcode,
    //     qty : product.quantity,
    //     price :product.price,
    //     regular_price : product.regular_price,
    //     tags : product.tags, 
    //     images : product.images,
    //     attributes : product.attributes,
    //     status : product.status           
    // }

    $.ajax({
        url : '/admin/products/save',
        type : 'POST',
        data :formData,
        cache:false,
        contentType: false,
        processData: false,  
        success:function(res){ console.log(res) }
    })
}
// events //
hasHariantElement.addEventListener('change', function(){  
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
})

function removeOption(elem){
    let id = elem.getAttribute('id')
    arrRemove(product.attributes, 'id', id)   
    elem.parentElement.remove()
}
function addVariantEvent(event){
    if (event.key === "Enter") {    
        let value = event.target.value
        let id = parseInt(event.target.getAttribute('data-id')) 
        if(value == "" ) return   
        if(existVariantItem(id,value)) return event.target.value = ""  
        addVariantItem(id,value)   
        
        
        let wrapper = event.target.parentElement.querySelector('.variants-list-wrapper')
        wrapper.innerHTML += createVariantHTML(value, id) 
        event.target.value = ""         
    } 
}

function removeVariantItem(elem){
    let item  = elem.getAttribute('name')
    let variant_id = parseInt(elem.getAttribute('data-id')) 
    deleteVariantItem(variant_id,item)
    elem.parentElement.remove();  
}

btnaddvariant.addEventListener('click', function(){    
    let selected =  parseInt(selectAttributes.value)
    if(arrContains(product.attributes, 'id', selected)) return alert('varaint already selected')        
    product.attributes.push({ id : selected, variants : [] })    
    optionsWrapper.innerHTML += createOption()
})


/// file uploader
const fileProductImage  =  document.getElementById('file-product-image')
const fileImageGallery  =  document.getElementById('file-image-gallery')
const imagegallery = document.querySelector('.image-gallery')
let path = "";


// function loadGalleryImages(images){    
//     images.forEach(image => {
//         imagegallery.innerHTML += `<div class="image">
//                                         <img src="${image.path}" alt="">                                   
//                                         <span onclick="removeGalleryImage(this, ${image.id})" class="remove"><i class="fas fa-times"></i></span>
//                                     </div>`
//     }) 
// } 



// function loadImage(image){
//     const html = `<div class="image">
//                         <img src="${image.path}" alt="">                                   
//                         <span onclick="removeProductImage(this, ${image.id})" class="remove"><i class="fas fa-times"></i></span>
//                     </div>`
//     const imageElement = document.querySelector('.image-product .image')
//     imageElement.innerHTML += html;
    
// }

function previewImageProduct(e){ 
    const html = `<div class="image">
                        <img src="${e.target.result}" alt="">                                   
                        <span onclick="removeProductImage(this)" class="remove"><i class="fas fa-times"></i></span>
                    </div>`
    const imageElement = document.querySelector('.image-product .image')
    imageElement.innerHTML += html;    
}

function previewImagesGallery(e){   
    const html =`<div class="image">
                    <img src="${e.target.result}" alt="">                                   
                    <span onclick="removeGalleryImage(this,)" class="remove"><i class="fas fa-times"></i></span>
                </div>`
   imagegallery.innerHTML += html;  
}


function removeGalleryImage(elem, id, type){ 
    const res = deleteImage(id)
    if (!res) return   
    arrRemove(product.images, 'id', id)
    elem.parentElement.remove()   
}
function removeProductImage(elem,id){  
    const res = deleteImage(id)
    if (!res) return
    product['image'] = {id : '', path : ''}  
    elem.parentElement.querySelector('img').remove()
    elem.remove()   
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
    const form = document.getElementById('form')
    var formData = new FormData(form);
    formData.append('multiple', multiple)  
    for(let i in imagesToUpload){       
        formData.append('images[]', imagesToUpload[i])
    } 
    $.ajax({
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(e) {             
                const percent = e.lengthComputable ? (e.loaded / e.total) * 100 : 0 
                progressStart(element,percent.toFixed(2)) 
           }, false);     
           return xhr;
        },
        url : `/admin/image/uploads`,
        type : 'post',
        data : formData,
        cache:false,
        contentType: false,
        processData: false,   cache:false,
        contentType: false,
        processData: false,       
        success:function(res){
            progressStop(element)  
            imagesToUpload = [] 
            if(multiple){
                res.images.forEach(image => { product['images'].push(image) })
                loadGalleryImages(res.images)
               
                return;
            }         
            loadImage(res.images[0])         
            product['image'].id = res.images[0].id  
            product['image'].path = res.images[0].path
        },      
    })
}

fileProductImage.addEventListener('change', function() {
    readFile(this.files[0],previewImageProduct)
    product.image = this.files[0]
 
})

fileImageGallery.addEventListener('change', function(){ 
    const files  =Array.from(this.files)
    if(files.length == 0) return
    files.forEach((file) => {
        product.images.push(file)
        readFile(file, previewImagesGallery)
    })
})





// fileProductImage.addEventListener('change', function() {/
//     // const progressWrapper = document.querySelector('.progress-image') 
//     // imagesToUpload.push(this.files[0])    
//     // uploads(progressWrapper,false)  
// })

// fileImageGallery.addEventListener('change', function(){ 
//    
//    Array.from(this.files).forEach((file,index) => { 
//     imagesToUpload.push(image)      
//    }) 

//    console.log(product.images)
// //    const wrapper = document.querySelector(`.progress-images`)  
// //    imagegallery.style.minHeight = "200px"
// //    uploads(wrapper) 
// })

btnsave.addEventListener('click', function(){
    fillproductproties();
    save();
})







