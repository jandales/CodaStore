

const wishlistbtn = document.getElementById('wishList')
const ulWishList = document.getElementById('ul-wistlist')

let token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content')

if(wishlistbtn){


wishlistbtn.addEventListener('click', function(){


  
        ulWishList.innerHTML = ""
    

    $.ajax({                
        url: "/wishlist",
        type: 'get',
        success: function(response) {                            
           
            
            response.forEach(wishlist => { 
                createwishlist(wishlist)
            });                            


        },
    });
 
});

}

function createwishlist(wishlist){





    let li = document.createElement('li');
    li.id = 'wishlist-'+wishlist.id
    let wishlistdiv = document.createElement('div');
    wishlistdiv.className = 'wishlist'

    let wishlistimage = document.createElement('div');
    wishlistimage.className = 'wishlist-image'
     let img = document.createElement('img');

    img.src = "/" + wishlist.product.imagePath;
    wishlistimage.appendChild(img);
    
    let wishlistdetail = document.createElement('div');            
    wishlistdetail.className = 'wishlist-detail'

    let info = '<a>'+ wishlist.product.name +'</a><p>Php '+ wishlist.product.prices.selling +' </p>'
    wishlistdetail.innerHTML = info 

    let inline = document.createElement('div');
    inline.className = 'inline'
    inline.innerHTML ='<button class="mr-1">Add to cart</button> <button onclick="removeWishlist('+ wishlist.id +')">Remove</button>'
   
    wishlistdetail.appendChild(inline);


    wishlistdiv.appendChild(wishlistimage);
    wishlistdiv.appendChild(wishlistdetail);

    li.appendChild(wishlistdiv);

    ulWishList.appendChild(li);
}


function removeWishlist(wishlist){

    $.ajax({
        url : '/wishlist/delete/' + wishlist,
        method : 'delete',
        data : {
            _token : token,        
        },
        success : function (response) {
            removeElementWishlist(wishlist);
        }
    });
    
}

function removeElementWishlist(id)
{

    const child = document.getElementById('wishlist-'+id)
    ulWishList.removeChild(child);
}


