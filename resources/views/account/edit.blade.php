@extends('layout.front.app')

@section('content')  

    
        <div class="container">

            <div class="flex full-width mt-3 mb-3"> 
                <div class="col1">
                    @include('layout.front.sidebar')
                </div>
                <div class="col2">
                    <div class="card no-border pad-2 bg-grey">
                        <div class="card-heading">
                            <h2>Edit Profile</h2>
                        </div>
    
                        <div class="flex">   
                             <div class="form mt-1">
                                    @if (session('status'))
                                       <div class="alert alert-success alert-bordered mt-1 mb-1">{{ session('status')}}</div>
                                    @endif
                                   <form  id="form" action="{{ route('users.update')}}" method="post">
                                       @csrf
    
                                       <div class="profile flex wrap">
                                            <div class="form-block">
                                                <label>Name</label>
                                                <input type="text" class="user-name"  name="name" value="{{ auth()->user()->name }}">
                                            </div>         
                                                                    
                                            <div class="form-block">
                                                <label>Email <span>( email cannot be changed)</span></label>
                                                <input type="text" name="email" value="{{ auth()->user()->email }}" disabled>
                                            </div>
                            
                                            <div class="form-block">
                                                <label>Phone Number</label>
                                                <input type="text" class="user-contact" name="contact" value="{{ auth()->user()->contact }}">
                                            </div> 
                                            <div class="form-block">
                                                <label>Date of Birth</label>
                                                <input type="date"  id="dateofbirth" name="dateofbirth" value="{{ auth()->user()->dateofbirth }}">
                                            </div> 
                                          
                                            <div class="form-block">
                                                <label>Age</label>
                                                <input type="text" id="age"  name="age" value="{{ auth()->user()->age }}">
                                            </div> 
                                       </div>
                                      
                                       <br>                                 
                                       
                                       <div class="flex">
                                        <button id="submit" class="button p-15 w-3 dark">SAVE</button> 
                                        <a href="{{ url()->previous() }}"  class="btn p-15 w-3 btn-danger ml-1">CANCEL</a>   
                                       </div>
                                                        
                                       
                                   </form>
                               </div> 
    
                        </div>
                       
    
    
                         
                        
    
                    
                    </div>
                </div>
             

               
             



            </div>

        </div>
    
       <script>
        
        const dateInput =  document.getElementById('dateofbirth');

        document.addEventListener('DOMContentLoaded', function(){

            const date = new Date();
            let year =   date.getFullYear() +"-"+   date.getMonth() + 1 + "-"+ date.getDate()            
            dateInput.value = year;

            getAge();
            

        });

        dateInput.addEventListener('change', function(){
            getAge();
        });
        
      



        
        function getAge(){   
          
            let dateofbirth =  new Date(dateInput.value).getFullYear();
            let presentDate =  new Date().getFullYear();            
            
            if(dateofbirth == null) return;

            let age = parseInt(presentDate) - parseInt(dateofbirth);

            let ageInput = document.getElementById('age').value = age;
        }

        // const submit =  document.getElementById('submit');
        
        // submit.addEventListener('click', function(e){
        //      e.preventDefault();
        //   getAge();
        // });
        

        //   function placeOrder(){
 
  

        //         const token =  document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        //         const url =  document.getElementById('form').getAttribute('action');
            

        //         $.ajax({
        //             url : url,
        //             method : 'POST',
        //             data : {
        //                 _token : token,                              
        //             },
        //             success : function(response){
        //                 consolog.log(response);
        //             }        
        //         })
        //     }

      </script> 
    
   @endsection