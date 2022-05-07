document.addEventListener('DOMContentLoaded', function(){
                
        // execute when page loaded
    
        /// execute to create product rating
        productRating();
        
        /// execute to create reviewer rating
        reviewerRating()                
       
    
})

function reviewerRating()
{
      // get the element
    const reviewer =  document.querySelectorAll('.ul-rate-star')

    reviewer.forEach(review => {
        // get the product rating from the element    
        let  rating = review.getAttribute('rate-star') 
        // creating the review rating
        createStars(rating, review);                    
    })
}           

function productRating()
{
    // get the element
    const productrating = document.querySelectorAll('.productRating'); 

    productrating.forEach(item => {
         // get the product rating from the element
        let rating = item.getAttribute('rating');
         // creating the rating
        createStars(rating, item); 
    })
    
   
}

function createStars(rating, elemContainer){

    //default star value
    let star = 5;
    // creating solid stars
    for(i = 0; i < rating; i++ ){
         //create an element list item
        let li = document.createElement('li');
        // set li elemenent a innner html 
        li.innerHTML = '<i class="fas fa-star" aria-hidden="true">';  
        //append star in the elemContainer                   
        elemContainer.appendChild(li);  
    }

    // creating transparent star
    for( j = 0; j < star - rating; j++ ){ 

        //create an element list item
        let li = document.createElement('li');
         // set li elemenent a innner html 
        li.innerHTML = '<i class="far fa-star" aria-hidden="true">';   
        //append star in the elemContainer                          
        elemContainer.appendChild(li);

    }
} 


const editbtn = document.querySelector('.edit-review')

const textEditor = document.getElementById('text-editor')

const addreviewwrapper = document.querySelector('.add-review-wrapper')

if(editbtn){

    editbtn.addEventListener('click', function() {

        //  /// scroll to texteditor
        //  scrolldown();    
        //set the review text tot texteditor
        textEditor.innerText = editbtn.getAttribute('value')

        let rating = this.getAttribute('rate');

        /// set the review rate into input element
        document.querySelector('[name="rate"]').value = rating
        
        /// ser rating star
        const rate = document.querySelectorAll('.rate')
        rate.forEach(elem => {
            if(elem.getAttribute('rate') <= rating)                      
            {
               elem.firstElementChild.classList.replace('far' , 'fas');
               return
            }
        })
       
        
    
    })
}









