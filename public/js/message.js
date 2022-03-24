



function showMessage(status, message)
{ 

    const mgsSuccess =  document.getElementById('msg-popup')
    let _title = mgsSuccess.querySelector('.title')
    _title.textContent = status
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

function icon(value)
{
    let icon = "";  

    if(value == "info"){
       return icon = "fa-info-circle";            
    }

    if(value == "success"){
        return icon == "fa-check-circle";
    }

    if(value == "danger" || "warning"){
        return icon == "fa-exclamation-circle";
    }
}


const msgClose = document.querySelectorAll("[msg-close]");

msgClose.forEach(close => {
    close.addEventListener('click', function(){
    const overlay = close.closest('.pop-msg-overlay')
        overlay.style.display = 'none';
        
    })
})