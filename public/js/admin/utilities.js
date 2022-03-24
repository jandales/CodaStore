function progressStart(element, percent){  
    const progessbar = element.querySelector('.progress-bar')
    element.style.display = 'flex'
    progessbar.style.setProperty('--width', percent)
}
function progressStop(element){ 
    element.style.display = 'none'
}
 function readFile(input,callback) {
    if (!input) return  
    var reader = new FileReader()
    reader.onload = callback
    reader.readAsDataURL(input)
}









// const progessbar = document.querySelector('.progress-bar')
// const progessbarWrapper = document.querySelector('.progress-bar-wrapper')

//     // setInterval(() => {
//     //     const computedStyle = getComputedStyle(progessbar)
//     //     const width = parseFloat(computedStyle.getPropertyValue('--width')) || 0
//     //     if(width == 100) return  progessbarWrapper.style.display = 'none'
//     //     progessbar.style.setProperty('--width', width + .1)        
//     // },.5)
// }