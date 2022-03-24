import { generatePassword } from '/js/admin/module/password-generator.js'

const inputPassword = document.querySelector('input[name="password"]');
inputPassword.value = generatePassword()

document.getElementById('btngeneratePassword').addEventListener('click', event => {
    event.preventDefault();
    inputPassword.value = generatePassword()
})   

document.getElementById('set-new-password').addEventListener('click', event => {
    event.preventDefault();
    const passwordWrapper = document.getElementById('password-wrapper');  
    
    if (passwordWrapper.style.display == 'none'){
        passwordWrapper.style.display = 'block'
        document.getElementById('is-set-new-password').value = 1;
        inputPassword.value = generatePassword()
        return;
    }    
    
    document.getElementById('is-set-new-password').value = 0;
    passwordWrapper.style.display = 'none'
    inputPassword.value = ''  

})

document.getElementById('btn-send-password-reset').addEventListener('click', event => {
    event.preventDefault();
    const form = document.getElementById('formSendPasswordResetLink');
    form.submit();       
})

