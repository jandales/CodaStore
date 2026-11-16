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





export function showMessage(status, message)
{ 

    const mgsSuccess =  document.getElementById('msg-popup')
    let _title = mgsSuccess.querySelector('.title')
    const titles = { success: 'Done', info: 'Please note', warning: 'Please check', danger: 'Please check' }
    _title.textContent = titles[status] || status
    let _message = mgsSuccess.querySelector('.message') 
    _message.textContent = message;

    let msgContent = mgsSuccess.querySelector('.msg-content');
    msgContent.classList.replace(msgContent.classList[1], status); 

    let msgIcon = msgContent.querySelector('.fas');
    msgIcon.classList.replace(msgIcon.classList[1], icon(status));

    let msgButton  = mgsSuccess.querySelector('.btn');         
    msgButton.classList.replace(msgButton.classList[1], 'btn-'+status)      
            
    mgsSuccess.style.display = "block";   
    
   
}

export function icon(value)
{
    let icon = "";  

    if(value == "info"){
       return icon = "fa-info-circle";            
    }

    if(value == "success"){
        return icon = "fa-check-circle";
    }

    if(value == "danger" || value == "warning"){
        return icon = "fa-exclamation-circle";
    }

    return "fa-info-circle";
}


