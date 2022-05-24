@extends('layout.admin')

@section('content')


    <div class="page-title">
        <h1>Users Info</h1>   
        <a href="{{route('admin.users') }}"  class="btn btn-danger">Back</a>     
    </div>       
        
        <div class="page-container m-t-2">  
            <div class="right-column">
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Profile Picture</label>
                    </div>
                    <div class="panel-body">                  
                        <div class="avatar-big  center">
                            <img  class="round" src="/{{ $user->avatar() }}" alt="" srcset=""> 
                         </div>  
                         <div class="centered flex-column">
                             <h4>{{ empty($user->fullName()) ? $user->fullName() : $user->username  }}</h4>
                             <label for="" class="m-t-1">is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</label>                            
                             <a href="{{route('admin.users.edit',[$user->encryptedId() ]) }}"  class="btn btn-primary m-t-1">Edit user</a> 
                         </div>                 
                    </div>
        
                </div>
            </div>
        <div class="left-column"> 
                
           
    
            <div class="panel">
                <div class="panel-heading">
                    <label for="" class="panel-title">Name</label>
                </div>
                <div class="panel-body">
                    <div class="form-block">
                        <label for="name">Username</label>
                        <input type="text" name="username" value="{{ $user->username }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Role</label>
                        <input type="text" name="role" value="{{ $user->userRole() }}">   
                                     
                    </div>


                    <div class="form-block">
                        <label for="name">Email (required)</label>
                        <input type="text" name="email" value="{{ $user->email }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">First Name</label>
                        <input type="text" name="firstname" value="{{ $user->firstname }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Last Name</label>
                        <input type="text" name="lastname" value="{{ $user->lastname }}">                 
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-heading">
                    <label for="" class="panel-title">Contact Info</label>
                </div>
                <div class="panel-body">                  
                    <div class="form-block">
                        <label for="name">Email (required)</label>
                        <input type="text" name="email" value="{{ $user->email }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Phone Number</label>
                        <input type="number" name="contact_number" value="{{ $user->contact_number }}">                 
                    </div>                    
                </div>

            </div>

            <div class="panel">
                <div class="panel-heading">
                    <label for="" class="panel-title">Address</label>
                </div>

                <div class="panel-body">
                    <div class="form-block">
                        <label for="name">Street</label>
                        <input type="text" name="street" value="{{ $user->street }}">                        
                    </div>

                    <div class="form-block">
                        <label for="name">City / Town</label>
                        <input type="text" name="city_town" value="{{ $user->city_town }}">                        
                    </div>

                    <div class="form-block">
                        <label for="name">Postcode / ZIP</label>
                        <input type="text" name="postalcode_zip" value="{{ $user->postalcode_zip }}">                        
                    </div>

                    <div class="form-block">
                        <label for="name">Country / Region</label>
                        <input type="text" name="country_region" value="{{ $user->country_region }}">                        
                    </div>                    
                </div>
            </div>

       

        </div>

      
      
        </div>



        




@endsection