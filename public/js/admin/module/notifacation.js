
const notifyMessageElement  = getElementById('notify-message')

export function successMessage(message) {  
    notifyMessageElement.innerHTML = '' 
    notifyMessageElement.innerHTML += `<div class="alert alert-success">
                                            <div class="flex justify-content-space-between">
                                                <label class="message">${message}</label>
                                                <span class="closebtn" onclick="errorClose(this)"><i class="fas fa-times"></i></span>
                                            </div> 
                                        </div>`
}

export function errorMessage(errors){ 
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
