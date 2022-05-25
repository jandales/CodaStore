const spinnerWrapper = document.querySelector('.spinner-wrapper');
const spinner = document.querySelector('.spinner');
const successElement = document.querySelector('.loading-success');
const btn = document.getElementById('btn-payment-completed');
let redirectTo;
const FIXED = 'fixed';
const FLEX = 'flex';
const NONE = 'none';

export function startSpin(){
    start();
    spinnerWrapper.classList.add(FLEX);
    if(spinner.classList.contains('hidden')) {
        spinner.classList.remove('hidden');
    }
    spinner.classList.add('start');
 
}

export function stopSpin(){
    spinnerWrapper.classList.remove(FLEX);  
    stop();
}
export function spinCompleted(redirect = false, url){
    stop();
    redirectTo = url;
    successElement.classList.add(FLEX);
    if(!redirect){
        spinnerWrapper.classList.add(FLEX);    
    }
}  

function stop(){      
    spinner.classList.remove('start');
    spinner.classList.add('hidden');
}

function start(){
    successElement.classList.remove(FLEX);
}

if(btn){
    btn.onclick =  function(e){
        e.preventDefault();
            window.location.href = redirectTo;
    }
}


