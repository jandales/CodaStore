
function readURL(input, elem ) {   
    
    if (input.files && input.files[0]) {

    var reader = new FileReader()

    reader.onload = function (e) { 
        elem.setAttribute('src', e.target.result);
    }

    reader.readAsDataURL(input.files[0])

    }

}

const btnfile = document.getElementById('btn-file')
const file = document.getElementById('file')
const inputText =  document.getElementById('input-name') 
const defualtImage = document.getElementById('upload-default-image')

if(btnfile){
    btnfile.addEventListener('click', function(){
        file.click();
    })
}

if(file){
    file.addEventListener('change', function() {      
        if(file.value){   
             const image =  document.getElementById('avatar-image');  
             inputText.value = file.files[0].name 
             readURL(file,image)
        }    
     })
}

if(defualtImage){
    defualtImage.addEventListener('click', function(){
        const image =  document.getElementById('avatar-image')
        const fileinput = document.getElementById('file')
    
        image.setAttribute('src', '/img/avatar.png')
        inputText.value = ""   
        fileinput.value = null 
    });
    
}






