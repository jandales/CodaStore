            // elements
            // get thumbnails element
            const thumbnails = document.querySelectorAll('.thumbnail')
            // get arrow next button
            const arrowNext = document.querySelector('.arrow-right')
            // get arrow left button
            const arrowLeft = document.querySelector('.arrow-left')
            // variable images array
            const images = []
            // variable index use for set index in images array
            let index = 0 

            // load when pageload
            document.addEventListener('DOMContentLoaded', function(){
                getImages();
                // setFullImage(images[0].src); 
                // images.forEach(image => {
                //     image.element.addEventListener('click', () => {
                //         setFullImage(image.src);
                //         thumbnailFucos(image.element);
                //     })
                // })               
            })

            arrowNext.addEventListener('click', () => {
                // check if images is equal to index
                if(index === images.length -1 ){
                    // set index to first image
                    index = 0;
                }
                else{
                    // increate index
                    index += 1;
                } 
                // set thumbmail fucos
                // thumbnailFucos(images[index].element)
                // set image to full images
                setFullImage(images[index].src);

            })

            arrowLeft.addEventListener('click', () => {
                 // check if images is equal to index
                if(index === 0){
                    // set index to last images 
                   index = images.length;
                }
                // decreament index
                index -= 1;
                // set thumbmail fucos
                // thumbnailFucos(images[index].element)
                 // set image to full images
                setFullImage(images[index].src);

            })

            function thumbnailFucos(elem){                
                removeFucos();
                let overlay = elem.querySelector(".thumbnail-overlay");
                overlay.style.borderColor = "#ddd"
               
            }

            function removeFucos(){
                thumbnails.forEach(elem => {
                    //remove borders
                    let overlay = elem.querySelector(".thumbnail-overlay");
                    overlay.removeAttribute("style");
                   
                });
            }

            // method get images
            function getImages(){
                // get images from thumbnails element
                thumbnails.forEach(image => {  
                          // get image
                          let element = image
                          let src = image.firstElementChild.getAttribute('src')
                          // push image to array images
                          images.push({element, src})
                });
            }

            function setFullImage(src){
                    // get images container
                    const fullimage = document.querySelector('.full-image'); 
                    // get img tag                 
                    const image = document.getElementById('fullImage');
                    // remove image from container
                    fullimage.removeChild(image)
                    // create img tag
                    // set properties
                    let img =  document.createElement('img')
                    img.src = src
                    img.id = 'fullImage'
                    img.className = "fadeIn"
                    // appen img tag to images container
                    fullimage.appendChild(img)                    
                    // image.src = src;
                    // image.classList.add("fadeIn");
            }


            function selected(elem){
                
                let src = elem.querySelector('img').getAttribute('src');
                // thumbnailFucos(elem);
                setFullImage(src);
              
            }