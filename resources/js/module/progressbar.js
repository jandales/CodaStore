export  function progressBarStart(element, percent){ 
    element.style.display = 'flex' ; 
    const progessbar = element.querySelector('.progress-bar')    
    progessbar.style.setProperty('--width', percent)
}
export function progressBarStop(element){ 
    element.style.display = 'none';
}