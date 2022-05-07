window.addEventListener("scroll", event => {
    
    const header = document.querySelector(".header");
    if(header){
        if(window.pageYOffset > 200)
            header.classList.add('sticky-header');    
        else
            header.classList.remove('sticky-header');    
    }

});