@extends('layout.admin')

@section('content')
<div class="container">

    <div class="page-title">
        <h1>Edit Users</h1> 
        <a href="{{route('admin.users') }}"   class="btn btn-danger">Cancel</a>       
    </div>
    @if(session('success'))
        <div class="alert alert-success mt10">{{ session('success') }}</div>                
    @endif 
    <form id="form" method="POST"  action="{{route('admin.users.update',[$user->encryptedId() ])}}">
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
                        <label for="name">Role</label>
                        <select  class="capitalized" name="role" > 
                            <option value="0" {{ $user->role == 0 ? 'selected' : '' }}>Employee</option>  
                            <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>Adminstrator</option>                                                                              
                        </select>                  
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

        <div class="right-column">
            <div class="panel">
                <div class="panel-heading">
                    <label for="" class="panel-title">Profile Picture</label>
                </div>
                <div class="panel-body">                  
                    <div class="avatar-big center">
                       <img src="{{ $user->avatar() }}" alt="" srcset="">
                    </div>                  
                </div>
    
            </div>

            <div class="panel">
                <div class="panel-heading">
                    <label for="" class="panel-title">Password</label>
                </div>
                <div class="panel-body">                       
                    <div class="form-block">
                        <button id="set-new-password" type="create" class="btn btn-primary">Set new password</button>   
                        <input type="hidden" id="is-set-new-password" name="isSetNewPassword"  value="0">                    
                    </div>  

                    <div id="password-wrapper" style="display:none;" class="form-block">
                        <div class="form-block">
                            <input type="text" name="password" value="" style="margin-right: 20px;"> 
                            <button id="btngeneratePassword" type="create" class="btn btn-primary mt10">Generated Password</button> 
                        </div>
                    </div>

                    <div class="form-block">
                        <button id="btn-send-password-reset" type="create" class="btn btn-primary">Send Password reset Link</button>
                    </div> 
                </div>
            </div>         
            <div class="flex justify-content-end gap10">
                <button  type="create" class="btn btn-primary">Save Changes</button>              
            </div>    
        </div>
      
        </div>


    </form>
    <form id="formSendPasswordResetLink" action="{{ route('admin.users.sentPasswordResetPassword', [$user->encryptedId()]) }}" method="post">
        @csrf
    </form>       


</div>

@endsection