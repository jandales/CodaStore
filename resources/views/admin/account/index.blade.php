@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Profile Information</h1>
    <a href="{{ url()->previous() }}" class="btn btn-danger">Back</a>
</div>

<form id="form" method="POST"  action="{{route('admin.users.update',[$user->encryptedId()])}}"  enctype="multipart/form-data">
    @csrf
    @method('put')
    
    <div class="mt10">
        @if ($errors->any())  
            @foreach ($errors->all() as $error)
                <div class="alert alert-danger mt10">
                    {{ $error }}
                </div>
            @endforeach
        @endif
    </div> 
    
    <div class="page-container m-t-2">  
        <div class="right-column">
            <div class="panel">                
                <div class="panel-body">                  
                    <div class="avatar-big relative center">
                       <img id="avater-image" class="round" src="{{ $user->imagePath }}" alt="" srcset="">                       
                        <label class="span-camera around">
                            <i class="fas fa-camera"></i>
                            <input  type="file" id="file-upload" name="image" style="display:none;">
                        </label> 
                    </div>  
                    <div class="m-t-1 centered flex-column">
                        <h4>{{ empty($user->fullName()) ? $user->fullName() : $user->username  }}</h4>
                        <label for="" class="m-t-1">is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s</label>
                        <button id="change-password"  class="btn btn-primary m-t-2">Change Password</button>
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
                    <input type="text" name="username" value="{{ $user->username }}" disabled>                 
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
                    <input type="email" name="email" value="{{ $user->email }}" disabled>                 
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
        <div class="flex justify-content-end gap10">
            <button id="btnsave" type="create" class="btn btn-primary">Save Changes</button>              
        </div> 

   

    </div>

   
    </div>


</form>

<div class="modal" id="modal-change-password">
    <div class="modal-content">
        <div class="modal-title">
            <h1 for="">Change Password</h1>
            <span class="modal-close"><i class="fa fa-times"></i></span>
        </div>
        <div id="notify-message" class="m-t-1">
          
        </div>
        <form id="form-update-password" action="{{ route('users.updatePassword', [$user->encryptedId()]) }}" class="m-t-2" method="post">
            @csrf
            @method('PUT')
            <div class="form-block">
                <label for="name">New password</label>
                <input type="password" name="password" value="">                 
            </div>

            <div class="form-block">
                <label for="name">Confirm Password</label>
                <input type="password" name="password_confirmation" value="">                 
            </div>

            <div class="form-block">
                <label for="name">Enter Current Password</label>
                <input type="password" name="validator" value="">                 
            </div>
            <div class="flex justify-content-end gap10">  
                <button id="update-password" type="create" class="btn btn-primary">Save</button>              
            </div> 
        </form>
        


    </div>
</div>




@endsection