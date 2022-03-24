const addWishListbutton = document.querySelectorAll('.add-wish-list')
addWishListbutton.forEach(elem => {
    elem.addEventListener('click', function(){
        addWishList(elem)         
    })
})

function addWishList(elem){
    let product =  elem.getAttribute('data');    
    $.ajax({
        url : '/wishlist/' + product,
        type: 'post',
        data : { _token : _token },
        success : function(data){   
            cartWishlistCountToElement('.wishlist-count', getWislistCount())            
            if(data.status == "warning") return removeWishListIcon(elem)

            if(data.status == "success"){
                wishListIcons(elem)
                showMessage("success",data.message)               
            }          
        },
        error: function (xhr) {                    
          if (xhr.status == 500) return showMessage("danger",'Login to your account to add this to your wishlist')            
        }
      
    });

}

function wishListIcons(elem){
    elem.classList.add('ctheme')
    let i = elem.firstElementChild
    i.classList.replace('far','fas')
}

function removeWishListIcon(elem){
    elem.classList.remove('ctheme')
    let i = elem.firstElementChild
    i.classList.replace('fas','far')
};