const ratesbutton = document.querySelectorAll('.rate');
       

let rates = 0

ratesbutton.forEach(rate => {
    rate.addEventListener('click', function() {
        rates = rate.getAttribute('rate');
        document.querySelector('[name="rate"]').value = rates
        fillRateStar();
    })
});

function fillRateStar(){

    ratesbutton.forEach((rate) => {  rate.firstElementChild.classList.replace('fas' , 'far');  });

    for(i= 0; i < ratesbutton.length; i++){

            let rate = ratesbutton[i].getAttribute('rate');
            ratesbutton[i].firstElementChild.classList.replace('far' , 'fas');
            if(rate == rates) return;
            
    }
   
}


function scrolldown(){
    let offset  = addreviewwrapper.getBoundingClientRect()
    position  = offset.top + offset.height; 
    window.scroll(0,position);
}
