@extends('layout.admin')

@section('content') 

<h1>Products</h1>



        <div class="tabs m-t-2">
            <div class="form-header">
                <ul class="form-navigation">
                    <li><a href="{{ route('product.edit',[$product]) }}">Item Information</a></li>
                    <li><a href="{{ route('product.variants',[$product]) }}">Variants</a></li>
                    <li><a href="{{ route('stock.edit',[$product->stock]) }}">Quantity</a></li>
                    <li><a href="{{ route('price.edit',[$product]) }}">Pricing</a></li>
                    <li><a href="{{ route('product.download',[$product]) }}"  class="active">Image Upload</a></li>  
                </ul>
            </div>
            
            <div class="form-content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>                
                        @endif 
                <div class="tabs" background="bg-default">
                    <div class="tab-header">
                        <ul class="tab-nav">
                            <li><a href="#"  class="tabs-button" data-for-tab="1" >Photos</a></li>
                            <li><a href="#"  class="tabs-button" data-for-tab="2" >Upload</a></li>
                        </ul>
                    </div>
                    <div class="tabs-content bg-default" data-tab= "1">
                        
                        <div class="photos-container">
                               
                            @foreach ($product->photos as $photo)
                    
                                <div class="photos">
                                    @if ($photo->image == $product->imagePath)
                                     <span class="icon featured"><i class="fa fa-check" aria-hidden="true"></i></span> 
                                    @endif
                                                                       
                                    <img class="active" src="/{{$photo->image}}" alt="">
                                    <div class="action">
                                        <a  href="javascript:{}" onclick="url('post','/admin/products/photo/{{$photo->id}}/featured-image')"><span  class="icons bg-white cl-success"><i class="fa fa-check" aria-hidden="true"></i></span></a>
                                        

                                        <a href="javascript:{}" onclick="url('delete','/admin/products/photo/{{$photo->id}}/destroy')"><span  class="icons bg-white cl-danger"><i class="fa fa-times" aria-hidden="true"></i></span></a>
                                      
                                    </div>
                                </div>
                            @endforeach  
                            
                            <form id="url"  method="post">
                                @csrf                             
                            </form>
                           
                               
                        </div>     
                    </div>
    
    
                    <div class="tabs-content bg-default" data-tab= "2">
                        <form id="form" action="{{ route('product.upload',[$product])}}" method="POST" enctype="multipart/form-data">
                            @csrf  
            
                                    
                            <div class="form-block">                                        
                                <div class="input-group">                       
                                    <div class="input-group-append">
                                      <span class="input-group-text">
                                          <label>Choose Image<input  type="file" id="file-uploader" name="images[]" style="display:none;" multiple ></label>
                                     </span>
                                    </div>
                                </div>                              
                            </div> 
                            
                            <div class="upload-images-contaniner">
                                     
                            </div>
            
                            <div class="form-button m-t-2">
                                <button onclick="upload()" class="btn btn-primary mr-2">Save</button>
                                <button class="btn btn-danger">Cancel</button>
                        
                            </div>
                       
                        </form>
                    </div>
                </div>
            </div>
           


 
        
        </div>
        
<script>


function url(method, route){

    let form =  document.getElementById('url')

    if(method == 'delete'){
        let input = document.createElement('input')
        input.type = 'hidden'
        input.name= '_method'
        input.value = 'delete'
        form.append(input)       
    }

    form.setAttribute('action', route)
  
    form.submit() 
}



const fileFeatureUploader = document.getElementById('file-uploader')

let images = [];

function readImage(input) {
    if (!input.files) return;       
    Array.from(input.files).forEach((image, index) => {  
        index  = lastIndex(images)
        images.push({index,image})
        var reader = new FileReader()        
        reader.onload = function (e) {   
            let div = document.createElement('div');
            div.className = 'upload-images';
            div.id = index;
            div.innerHTML = '<span class="icon remove-image" id="'+ index  +'" onclick="remove('+ index +')"><i class="fa fa-times"></i></span><img id="feature_image" src="'+ e.target.result +'" alt="">'; 
            document.querySelector('.upload-images-contaniner').appendChild(div)        
        }
        reader.readAsDataURL(image) 
    });
}


const imageRemove =  document.querySelectorAll('.remove-image')

fileFeatureUploader.addEventListener('change', function(){
    readImage(this) 
})


function remove(id){ 
    const image = document.getElementById(id);
    const parent = document.querySelector('.upload-images-contaniner')
    let arr = images;   
    for( let i = 0; i < arr.length; i++){    
        if ( arr[i].index === id) {          
            arr.splice(i, 1); 
            parent.removeChild(image);    
        }
    }

}
function  lastIndex(arr) {
   
    let lastIndex = 0;

    if(arr.length != 0){
        i = arr.length - 1;
        lastIndex = arr[i].index + 1;     
    }

    return lastIndex;


    
}
function upload() {

    event.preventDefault();
    let form = document.getElementById('form')
         // define new form
    var formData = new FormData();   

    for(i = 0; i < images.length; i++)
    {    
        formData.append('#file-uploader', images[i].file);
    }

    form.submit();       
  
   
}


  

   
   
      
    




   

        </script>


@endsection