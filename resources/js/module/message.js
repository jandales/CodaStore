const notifyMessageElement  = document.getElementById('notify-message')

export function errorMessage(errors){ 
    notifyMessageElement.innerHTML = '' 
    if(errors.length == 0) return notifyMessageElement.innerHTML = ''  
    errors.forEach(error => {
        notifyMessageElement.innerHTML +=  `<div class="alert alert-danger">
                    <div class="w-12 flex justify-content-space-between">
                            <label class="message">${error.message}</label>
                            <span class="closebtn"><i class="fas fa-times"></i></span>
                    </div> 
                </div>`
             
    })   
    closeMessage(); 
}



export function successMessage(message) {  
    notifyMessageElement.innerHTML = '' 
    notifyMessageElement.innerHTML += `<div class="alert alert-success">
                                            <div class="w-12 flex justify-content-space-between">
                                                <label class="message">${message}</label>
                                                <span class="closebtn"><i class="fas fa-times"></i></span>
                                            </div> 
                                        </div>`
    closeMessage();
}


function closeMessage(){
    document.querySelectorAll('.closebtn').forEach(btnclose => {
        btnclose.onclick = function(){         
           btnclose.closest('.alert').remove();
        }
    })
}

