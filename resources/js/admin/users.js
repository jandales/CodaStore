

import generate from '../inc/password';


 function submitForm(route, method){
     const form = document.getElementById('form');
     const input = document.querySelector('input[name="_method"]');
     if(method != null) input.value = method; 
     form.setAttribute('action', route); 
     form.submit();       
 } 

 document.querySelectorAll('.delete').forEach(element => {
     element.addEventListener('click', event => {
         const route = element.getAttribute('data-url');         
         submitForm(route, 'delete');
     })
 });

 const btnuserDelete = document.getElementById('deleteSelected');
 const btnupdateSelecteItemRole = document.getElementById('update-selected-item-role-to');
 if (btnuserDelete){
     btnuserDelete.onclick = function(){   
        submitForm(this.getAttribute('data-url'), 'delete')
     }
 }

 if (btnupdateSelecteItemRole){
     btnupdateSelecteItemRole.onclick = function(){
        const form = document.getElementById('action-form');   
        const list = [];
        document.querySelectorAll('.childCheckbox').forEach(element => {           
            element.type = 'hidden'
            form.append(element)  
        });
        form.submit();
     }
 }







//create users
const btnsendPasswordReset =  document.getElementById('btn-send-password-reset');
const btnsetNewPassword = document.getElementById('set-new-password');
const inputPassword = document.querySelector('input[name="password"]');
const btngeneratePassword = document.getElementById('btngeneratePassword');

// inputPassword.value = generate();

if (btngeneratePassword){
    btngeneratePassword.onclick = function(e)  {
        e.preventDefault();
        inputPassword.value = generate();
    }    
}


if(btnsetNewPassword){
    btnsetNewPassword.onclick =  function(e){
        e.preventDefault();
        const passwordWrapper = document.getElementById('password-wrapper');  
        
        if (passwordWrapper.style.display == 'none'){
            passwordWrapper.style.display = 'block'
            document.getElementById('is-set-new-password').value = 1;
            inputPassword.value = generate();
            return;
        }    
        
        document.getElementById('is-set-new-password').value = 0;
        passwordWrapper.style.display = 'none'
        inputPassword.value = ''  
    }
}

if (btnsendPasswordReset) {
    btnsendPasswordReset.onclick =  function(e){
        e.preventDefault();
        const form = document.getElementById('formSendPasswordResetLink');
        form.submit();  
    }
}



