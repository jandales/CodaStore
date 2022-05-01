function deliver(){
    const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url : '/admin/orders/deliver',
        method : 'POST',
        data : {
            _token : token,
            _method : 'PUT'
        },
        success : function(response) {              
        }
    })
}


const userDropDown = document.querySelector('.user-image');
const ul = document.querySelector('.navbar-dropdownlist');

userDropDown.addEventListener('click', event =>{  
    toggleDropdown(); 
});

function toggleDropdown(){

    if(ul.classList.contains('show')){
        ul.classList.remove('show');
    }
    else{
        ul.classList.add('show');

    }
}


const menubar = document.querySelector('.menubar');
const menubarIcon = document.querySelector('.menubaricon');
const sidebar =  document.querySelector('.side-bar');

menubar.addEventListener('click', event => {
    
    if(sidebar.classList.contains('show')){
        sidebar.classList.remove('show')
        menubarIcon.classList.remove('fa-times');
        menubarIcon.classList.add('fa-bars');
      
    }
    else{
        menubarIcon.classList.remove('fa-bars');
        menubarIcon.classList.add('fa-times');
        sidebar.classList.add('show')


    }
});


const searchbar =  document.querySelector('.searchbar');
const searchbutton = document.querySelectorAll('.open-close-toggle-search-bar');

function open_close_search_Bar(){
   if(searchbar.classList.contains('show')){
       searchbar.classList.remove('show');
   }else{
    searchbar.classList.add('show');
   }
    
}

searchbutton.forEach(elem => {
    elem.addEventListener('click', event => {
        open_close_search_Bar();
    });
});


 const btnEditProfile = document.querySelector('btn-edit-profile');
 const btnCancelProfile = document.querySelector('.btn-profile-cancel');

 if(btnEditProfile){
    btnEditProfile.addEventListener('click', () => {     


        const name = document.querySelector('input[name="name"]');
        const contact = document.querySelector('input[name="contact"]');
        const container = document.getElementById('btn-content');

        name.removeAttribute('disabled');
        contact.removeAttribute('disabled');

        container.style.display = "block";

 });
 }


if(btnCancelProfile){
    btnCancelProfile.addEventListener('click', event => {
        const name = document.querySelector('input[name="name"]');
        const contact = document.querySelector('input[name="contact"]');
        const container = document.getElementById('btn-content');    
        name.setAttribute('disabled','');
        contact.setAttribute('disabled', '');    
        container.style.display = "none";
        
     });
}
const ImageContainer = document.querySelector('.imageToUpload');
if(ImageContainer){
    ImageContainer.addEventListener('mouseover', function(){      
        const image =  document.getElementById('avatar-image');
        const overlay =  document.querySelector('.overlay');
        let value = image.getAttribute('src');
           
        if(value != '/img/avatar.png'){
            overlay.style.display= 'flex';
        }   
  }); 
}
 
if(ImageContainer){
    ImageContainer.addEventListener('mouseout', function(){
        const overlay =  document.querySelector('.overlay');
        overlay.style.display= 'none';
    });
}

const navDropdownbtn = document.querySelectorAll('.nav-dropdown')
navDropdownbtn.forEach(element => {
    element.addEventListener('click', function(){
        this.querySelector('.arrow').classList.toggle('rotate180')
        this.querySelector('.sub-menu').classList.toggle('show')        
    })
})




function errorClose(e){
    let alert = e.closest('.alert')
    alert.remove();
}

function alertMessage(message){
    let alert = document.querySelector('.alert-m')
    alert.style.display = 'block'
    alert.querySelector('.message').innerText =  message
}

function alertClose(e){
    let alert = e.closest('.alert-m');
    alert.style.display = 'none'    

}

function progressStart(element, percent){ 
    element.style.display = 'flex'  
    const progessbar = element.querySelector('.progress-bar')    
    progessbar.style.setProperty('--width', percent)
}
function progressStop(element){ 
    element.style.display = 'none'
}
function readFile(input,callback) {
    if (!input) return  
    var reader = new FileReader()
    reader.onload = callback
    reader.readAsDataURL(input)   
}

function getElementById(selector){
    return document.getElementById(selector)
}

const _token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const _put = "PUT"
const _delete = "DELETE"

function requestDelete(url){
    let result = null
    $.ajax({
        url : url,
        type : 'POST',
        data : {
            _token : _token,
            _method : _delete,
        },
        async : false,
        success:function(response){
            result =  response
        }
    })
    return result;
}

function requestLists(url){
    let result = null
    $.ajax({
        url : url,
        type : 'GET',        
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

// function requestGet(url){
//     let result = null
//     $.ajax({
//         url : url,
//         type : 'GET',        
//         async : false,
//         success:function(response){
//             result = response
//         }
//     })

//     return result;
// }

function requestSearch(url, input){
    let result = null
    $.ajax({
        url : url,
        type : 'GET',  
        data : {
            search : input,
        },   
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

function requestStore(url, data = {}){
    let result = null
    $.ajax({
        url : url,
        type : 'Post',
        data : data, 
        async : false,
        success:function(response){
            result = response
        }
    })

    return result;
}

// tabs
function runTabs(){
    document.querySelectorAll(".tabs-button").forEach(button => {  
        // create EventListener for everu tabs button
        button.addEventListener("click", () => { 
            // get parent element('tabs')
            const tabs = button.closest('.tabs') 
            // get current tab
            const tabNumber = button.dataset.forTab
            // get current tab content
            const tabToActivate = tabs.querySelector('.tabs-content[data-tab="'  + tabNumber  + '"]')
            // get if backround color is set
            const background = tabs.getAttribute('background')           
            /// reset .tabs-content
            reset(tabs,'.tabs-content-active', classList = ['tabs-content-active', background])
            // reset .tabs-button;
            reset(tabs, '.tabs-button', classList = ['tab-button-active', background])           
            // add class in tabs-content
            // tabs-content-active for active content
            tabToActivate.classList.add('tabs-content-active')
            //set background color for active content
            tabToActivate.classList.add(background)
            // add class in tabs-button
            // add class tab-button-active for active tab button           
            button.classList.add('tab-button-active') 
            // add background color for active button
            button.classList.add(background)          
        })
    })
}
// reset element 
// remove some classes (active, background)
function reset(parent,child,classList)
{    
    // find selector element in parent elemet
    // and iterate the child element
    parent.querySelectorAll(child).forEach(content => {
            // iterate the classes 
            classList.forEach(classitem => {               
                // if class is not null              
                if(classitem != null){
                    // remove class 
                    content.classList.remove(classitem);                    
                }               
            })
    });
}

// run automatically when the page run
document.addEventListener("DOMContentLoaded", () => {   
    deliver();
    runTabs();     
    document.querySelectorAll('.tabs').forEach(tabcontainer => {
        tabcontainer.querySelector('.tabs-button').click();
    });
});


require('../admin/users');
require('../admin/categories');
require('../admin/customers');
require('../admin/inventory');
require('../admin/reviews');
require('../admin/coupon');
require('../admin/products');




