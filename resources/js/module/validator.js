

function showErrors(errors) {
    if (errors === null || errors.length == 0) return;   
    errorRemove();
    errors.forEach(error => {
        setErrors(error.name, error.message)
    });
}

function setErrors(selector, message) {
    validatorInput(selector);
    validatorMessage(selector, message)
}

function validatorMessage(selector, message) {
    const validatorElement = document.querySelector([`[validator-for="${selector}"]`])
    if(!validatorElement) return;
    validatorElement.classList.add('show')
    validatorElement.innerHTML = message;
}

function validatorInput(selector) {
    const input = document.querySelector(`[validator-input="${selector}"]`);
    if(!input) return;    
    input.classList.add('border-danger')
}

export function errorReponse(xhr){
    console.log(xhr)
    const errors = [];
    const resErrors = xhr.responseJSON.errors;  
    for(const key in resErrors) errors.push({name : key, message : resErrors[key][0] }) 
    showErrors(errors)    
}

export function errorRemove()  {   
    document.querySelectorAll(`[validator-for]`).forEach(el => {
        el.classList.remove('show');
    });
    document.querySelectorAll(`[validator-input]`).forEach(el => {
        el.classList.remove('border-danger');
    });
}
