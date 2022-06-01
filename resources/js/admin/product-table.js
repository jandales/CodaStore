const btnSelectedDestroy = document.getElementById('deleteSelectedProducts');
const selectedItemUpdateStatus = document.querySelectorAll('[data-onClick]');

function formTableSubmit(route){
    const form = document.getElementById('form-table')
    form.setAttribute('action', route)
    console.log(form); 
    form.submit();
}

if(btnSelectedDestroy){
    btnSelectedDestroy.onclick = function() {
        formTableSubmit(this.getAttribute('data-url'));
    }
}
selectedItemUpdateStatus.forEach(item => {   
    item.onclick = function() {
        const route = item.getAttribute('data-url')
        formTableSubmit(route);
    }
})