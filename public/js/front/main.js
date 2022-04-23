const searchTrigger =  document.getElementById("btnsearch");
const navBar =  document.querySelector(".navbar");
const navSearch = document.querySelector(".navigation-search");
const closeSearch =  document.querySelector(".close-search");

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _put = "PUT"
const _delete = "DELETE"

searchTrigger.addEventListener('click', ()  => {

    if(navBar.style.display == "flex"){
        navBar.style.display = 'none';
        navSearch.style.display = "flex";
        document.getElementById("inputSearch").focus();
        return;
    }

    navBar.style.display = "flex";
    navSearch.style.display = "none";
});

closeSearch.addEventListener('click', () => {
    navBar.style.display = "flex";
    navSearch.style.display = "none";
});


var menu = document.getElementById("main-navigation");
var hamburger  = document.getElementById("hamburger-icon");
 

function menutoggle(){
    if(menu.hasAttribute('class')){      
        menu.removeAttribute('class','open'); 
        hamburger.setAttribute('class','fa fa-bars'); 

    }else{     
        menu.setAttribute('class','open');
        hamburger.setAttribute('class','fa fa-times')
    }
}    

const btnfilter = document.querySelector('.btn-filter');
const filterWrapper = document.getElementById('filter-wrapper')
if(btnfilter){
    btnfilter.addEventListener('click', function() {
        console.log(filterWrapper)
        filterWrapper.classList.toggle("visible")
    })
}



function removeAlert(){
    const alert = document.querySelectorAll('.alert-js')
    alert.forEach(elem => {
        elem.remove()
    });
}

const uploadBtn = document.querySelector(".btn-upload");
if(uploadBtn){
    uploadBtn.addEventListener('click', () => {
        const file = document.getElementById('file')  
        if(file.files[0] == null) return  alert("Please choose image")
    })
}

const file = document.getElementById('file');
let imageToUpload = [];
if(file){
    file.addEventListener('change', function() {
        
        if(file.value == null) return
 
        imageToUpload = file.files;
        readURL(file);
            
     })
}


function readURL(input) {

    
    if( input.files == null && input.files[0] == null) return 

    var reader = new FileReader();
      
    reader.onload = function(e){
        const avatarContainer = document.getElementById('avatarContainer')
        const image =  document.createElement('img')
        image.id = 'image-avatar'
        image.src = e.target.result
        avatarContainer.appendChild(image);
        createRemoveImageButton()           
    }        
    reader.readAsDataURL(input.files[0])
    removeImage()  

}

function createRemoveImageButton()
{
    const avatarContainer = document.getElementById('avatarContainer');
    const span = document.createElement('span');
    span.id = 'remove-image'
    span.style.zIndex = '2'
    span.innerHTML = '<i class="fa fa-times"></i>'
    span.addEventListener('click', function() {
        file.value = null;
        removeImage();
    })

    avatarContainer.append(span);
}

function removeImage(){
    const avatarContainer = document.getElementById('avatarContainer');
    const image = document.getElementById('image-avatar');
    const span = document.getElementById('remove-image')
    avatarContainer.removeChild(image);
    avatarContainer.removeChild(span)
}


function uploadAvatar(){
    event.preventDefault();
    let form  = document.getElementById('form-upload');
    form.submit()   
}

function alertMessage(message)
{
    let alert = document.querySelector('.alert')
    let span = alert.querySelector('.alert-message')

    if(message == null) return alert.classList.add('hidden')
    
    alert.classList.remove('hidden')
    span.innerHTML = message   
}

function closeAlertMessage(elem)
{
    let parent = elem.parentElement
    parent.classList.add('hidden')
}

document.addEventListener("DOMContentLoaded", () => {   
    getCartCount();
    getCarts();
    setCartCookie();

 })

function getCartCount(){
    $.ajax({
        url : '/cart/count',
        type: 'GET',
        async: false,  
        success : function(response){   
            cartCountToElement('.cart-count', response.cartItemsCount)         
        }
    });

}



function setCartCookie()
{
    $.ajax({
        url : '/create-cart-cookie',
        type : 'GET',
        async : false,
        success : function(response){

        } 
    });
}

function cartCountToElement(elemClass, count)
{
    let elem = document.querySelector(elemClass, count)
    if(count == 0) return elem.parentElement.classList.add('hidden')  
    elem.parentElement.classList.remove('hidden')
    elem.innerText = count;
}


function moneyFormatter(number){
    
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
    });

    return formatter.format(number);

}







