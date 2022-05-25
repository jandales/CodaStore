// const modalTrigger = document.querySelectorAll("[data-modal-target]");
const modalClose = document.querySelectorAll(".modal-close");
const closedrawer = document.querySelectorAll(".close-drawer");

// modalTrigger.forEach(elem =>{

//     elem.addEventListener('click', event => openModal(event.currentTarget.getAttribute("data-modal-target")));

// });

modalClose.forEach(elem =>{
    elem.addEventListener('click', event => openModal(event.currentTarget.closest(".modal").id));
});

closedrawer.forEach(elem =>{
    elem.addEventListener('click', event => openSidebarModal(event.currentTarget.closest(".modal").id));
});




function openModal(modalId){
    const modal = document.getElementById(modalId); 
    modal.style.display === "flex"    
    if(modal.style.display === "flex"){
        modal.style.display = "none";
        return;
    }
    modal.style.display = "flex";

}


function openSidebarModal(modalId){
    const modal = document.getElementById(modalId); 
    sidebar = modal.querySelector('.modal-sidebar'); 
    if(modal.style.display === "flex"){
        sidebar.classList.remove("right-0");
        setTimeout(function () {   modal.style.display = "none";  }, 1000);       
        return;
    }
    modal.style.display = "flex"; 
    setTimeout(function () { sidebar.classList.add("right-0"); },0);
}

export default openModal;







