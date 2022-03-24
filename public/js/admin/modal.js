// const modalTrigger = document.querySelectorAll("[data-modal-target]");
const modalClose = document.querySelectorAll(".modal-close");

// modalTrigger.forEach(elem =>{

//     elem.addEventListener('click', event => openModal(event.currentTarget.getAttribute("data-modal-target")));

// });

modalClose.forEach(elem =>{

    elem.addEventListener('click', event => openModal(event.currentTarget.closest(".modal").id));

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
