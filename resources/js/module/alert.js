export function alertMessage(message){
    let alert = document.querySelector('.alert')
    let span = alert.querySelector('.alert-message')

    if(message == null) return alert.classList.add('hidden')
    
    alert.classList.remove('hidden')
    span.innerHTML = message   
}

export function closeAlertMessage(elem){
    let parent = elem.parentElement
    parent.classList.add('hidden')
}

export function removeAlert(){
    const alert = document.querySelectorAll('.alert-js')
    alert.forEach(elem => {
        elem.remove()
    });
}