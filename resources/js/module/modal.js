export function openModal(modalId){
    const modal = document.getElementById(modalId); 
    modal.style.display === "flex"    
    if(modal.style.display === "flex"){
        modal.style.display = "none";
        return;
    }
    modal.style.display = "flex";

}


export function openSidebarModal(modalId){


    const modal = document.getElementById(modalId); 
    const sidebar = modal.querySelector('.modal-sidebar'); 
    if(modal.style.display === "flex"){
        sidebar.classList.remove("right-0");
        setTimeout(function () {   modal.style.display = "none";  }, 1000);       
        return;
    }
    modal.style.display = "flex"; 
    setTimeout(function () { sidebar.classList.add("right-0"); },0);
}







