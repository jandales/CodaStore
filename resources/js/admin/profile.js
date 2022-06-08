import {openModal} from '../module/modal'
import {successMessage, errorMessage } from '../module/message'
let errors = [];
function loadImage(e){ 
    const image = document.getElementById('avater-image');
    image.src = e.target.result;  
}

function readURL(input) {   
    const imageContainer  = document.getElementById('avater-image');
    if (input.files && input.files[0]) {

    var reader = new FileReader()

    reader.onload = function (e) { 
        imageContainer.setAttribute('src', e.target.result);
    }

    reader.readAsDataURL(input.files[0])

    }

}
const profileImageFileChange = document.getElementById('file-upload');

if(profileImageFileChange) {
    profileImageFileChange.addEventListener('change', function(e) { 
        readURL(this)   
    })
}

const changePassword = document.getElementById('change-password');
if(changePassword){
    changePassword.addEventListener('click', function(e) { 
        e.preventDefault();
        openModal('modal-change-password');
    })
}


function updatePassword(e)
{ 
    e.preventDefault();
    const form= document.getElementById('form-update-password')
    const url = form.getAttribute('action');
    var formData = new FormData(form);
    $.ajax({
        url : url,
        type : 'POST',
        data : formData,
        cache:false,
        contentType: false,
        processData: false,   
        success: function(res){
            successMessage(res.message)            
        },
        error:function(XMLHttpRequest){
             let resErrors =  XMLHttpRequest.responseJSON.errors      
             if(XMLHttpRequest.status === 422){
                 let errors = [];            
                 for(const key in resErrors) errors.push({ name : key , message  : resErrors[key] })
                 errorMessage(errors);
             }             
        }
    });
}

const btnUpdatePassword = document.getElementById('update-password');

if (btnUpdatePassword){
    btnUpdatePassword.addEventListener('click', updatePassword) 
}
