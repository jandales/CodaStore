import { cartCountToElement, setCartCookie, cartCount } from "./module/Cart";
import { openSidebarModal, openModal} from "../module/modal";

const searchTrigger =  document.getElementById("btnsearch");
const navBar =  document.querySelector(".navbar");
const navSearch = document.querySelector(".navigation-search");
const closeSearch =  document.querySelector(".close-search");

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _put = "PUT"
const _delete = "DELETE"
if(searchTrigger){
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
}

if (closeSearch) {
    closeSearch.addEventListener('click', () => {
        navBar.style.display = "flex";
        navSearch.style.display = "none";
    });
}



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

    avatarContainer.appendChild(span);
}

function removeImage(){
    const avatarContainer = document.getElementById('avatarContainer');
    const image = document.getElementById('image-avatar');
    const span = document.getElementById('remove-image')
    if(avatarContainer.hasChildNodes){
        avatarContainer.removeChild(image);
        avatarContainer.removeChild(span)
    }
  
}


function uploadAvatar(){
    event.preventDefault();
    let form  = document.getElementById('form-upload');
    form.submit()   
}



document.addEventListener("DOMContentLoaded", () => {   
    cartCountToElement(cartCount()) 
    setCartCookie();
})

// const modalTrigger = document.querySelectorAll("[data-modal-target]");
const modalClose = document.querySelectorAll(".modal-close");
const closedrawer = document.querySelectorAll(".close-drawer");
modalClose.forEach(elem =>{
    elem.addEventListener('click', event => openModal(event.currentTarget.closest(".modal").id));
});

closedrawer.forEach(elem =>{
    elem.addEventListener('click', event => openSidebarModal(event.currentTarget.closest(".modal").id));
});


const msgClose = document.querySelectorAll("[msg-close]");

msgClose.forEach(close => {
    close.addEventListener('click', function(){
    const overlay = close.closest('.pop-msg-overlay')
        overlay.style.display = 'none';
        
    })
})


const btnSorting =  document.getElementById('sorting');

if (btnSorting){
    btnSorting.onchange = function(){
        const id = this.selectedIndex;
        url = this.options[id].value;
        window.location.href = url;
    }
}


import Splide from '@splidejs/splide';

var splideCollectionElement = document.querySelector('.splide-collection');
if(splideCollectionElement){
    var splideCollection = new Splide( '.splide-collection', {
        type   : 'loop',
        perPage: 3,
        perMove: 1,   
        breakpoints: {
            1024: {
              perPage: 3,
              gap: 0,
             
            },
            767: {
              perPage: 2,
          
            },
            640: {
              perPage: 1,
        
            },
          },
        gap: '2em',
        pagination: false,
      } );
      splideCollection.mount();
}


const splideProductElement = document.querySelector('.splide-featured-products');

if (splideProductElement){
    var splide = new Splide( '.splide-featured-products', {
        type   : 'loop',
        perPage: 4,
        perMove: 1,
        breakpoints: {
            1024: {
              perPage: 3,
              gap: 0,
             
            },
            767: {
              perPage: 2,
          
            },
            640: {
              perPage: 1,
        
            },
          },
        gap: '2em',
      } );
      splide.mount();
    
}




























