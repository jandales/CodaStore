import { openModal } from './../module/modal';
function destroyReview(route){
    let form =  document.getElementById('destroy-reviews')
    form.setAttribute('action', route)  
    form.submit() 
}


const btnDestroyReviews = document.querySelectorAll('.destroy-review');
const btnDestroySelectedReview = document.getElementById('destroy-selected-review');

if(btnDestroySelectedReview){
    btnDestroySelectedReview.onclick = function(){
        const route = this.getAttribute('data-url');
        destroyReview(route);
    }
}
btnDestroyReviews.forEach(review => {
    review.onclick =  function(){
        const route = this.getAttribute('data-url')
        destroyReview(route);
    }
})



const readViewTrigger = document.querySelectorAll('.read-review');

readViewTrigger.forEach(review => {
    review.onclick = function() {
        
        const array = this.getAttribute('modal-data');       
        let data = JSON.parse(array);
    
    
        document.getElementById('user').value = data.user.name;
        document.getElementById('product').value = data.product.name;
        document.getElementById('comment').value = data.comments;
    
        const button = document.getElementById('modal-button')
    
        button.addEventListener('click', function(){
            destroy('/admin/reviews/block/'+ data.id);
        })
    
        if(data.block == 1){
            button.innerText = 'Unblock';
            button.classList.replace("btn-danger", "btn-primary")
            return;
        }
      
            button.innerText = 'Block';
            button.classList.replace("btn-primary", "btn-danger")
    
        openModal('modal-read-review')
    } 
})
     

