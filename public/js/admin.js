 

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

const notifyMessageElement  = getElementById('notify-message')

function errorMessage(errors){ 
    notifyMessageElement.innerHTML = '' 
    if(errors.length == 0) return notifyMessageElement.innerHTML = ''  
    errors.forEach(error => {
        notifyMessageElement.innerHTML +=  `<div class="alert alert-danger">
                    <div class="flex justify-content-space-between">
                            <label class="message">${error.message}</label>
                            <span class="closebtn" onclick="errorClose(this)"><i class="fas fa-times"></i></span>
                    </div> 
                </div>`
             
    })    
}



function successMessage(message) {  
    notifyMessageElement.innerHTML = '' 
    notifyMessageElement.innerHTML += `<div class="alert alert-success">
                                            <div class="flex justify-content-space-between">
                                                <label class="message">${message}</label>
                                                <span class="closebtn" onclick="errorClose(this)"><i class="fas fa-times"></i></span>
                                            </div> 
                                        </div>`
}


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

function selector(selector){
    return document.querySelector(selector);
}

function selectorAll(selector){
    return document.querySelectorAll(selector);
}

function input(name){
    return document.querySelector(`input[name="${name}"]`)
}









































