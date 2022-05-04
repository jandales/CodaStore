window.addEventListener("scroll", event => {
    
const header = document.querySelector(".header");
var page = document.querySelector(".homepage");

    if(window.pageYOffset > 200){

        header.classList.add('sticky-header');


    }
    else{
        header.classList.remove('sticky-header');
    }
});