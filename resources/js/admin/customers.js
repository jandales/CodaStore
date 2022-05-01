
function destroyCustomer(route){
    let form =  document.getElementById('destroy-customer')
    form.setAttribute('action', route)  
    form.submit() 
}


const btnDestroyUsers = document.querySelectorAll('.destroy-customer');
const customerSelectedDestroy = document.getElementById('customer-selected-destroy');

if(customerSelectedDestroy){
    customerSelectedDestroy.onclick = function(){
        const route = this.getAttribute('data-url');
        destroyCustomer(route);
    }
}
btnDestroyUsers.forEach(user => {
    user.onclick =  function(){
        const route = this.getAttribute('data-url')
        destroyCustomer(route);
    }
})


