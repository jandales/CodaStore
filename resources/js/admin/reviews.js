
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


