@extends('layout.admin')
@section('content')

<div class="container-800">
    <div class="page-title">
        <h1>Add Users</h1> 
        <a href="{{route('admin.users') }}"   class="btn btn-danger">Back</a>       
    </div>

    <div class="page-sub-title">
        <label for="">Create a brand new user and add them to this site.</label>
    </div>  

    <form id="form" method="POST"  action="{{route('admin.users.store')}}">
        @csrf
        <div class="mt10">
            @if ($errors->any())  
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger mt10">
                        {{ $error }}
                    </div>
                @endforeach
            @endif
        </div>      
        <div class="page-container  m-t-1"> 
                    
            <div class="panel">
               
                <div class="panel-body">
                    <div class="form-block">
                        <label for="name">Username (required)</label>
                        <input type="text" name="username" value="{{ old('username') }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Email (required)</label>
                        <input type="text" name="email" value="{{ old('email') }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">First Name</label>
                        <input type="text" name="firstname" value="{{ old('firstname') }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Last Name</label>
                        <input type="text" name="lastname" value="{{ old('lastname') }}">                 
                    </div>

                    <div class="form-block">
                        <label for="name">Password</label>
                        <div class="form-inline">
                            <input type="text" name="password" value="" style="margin-right: 20px;"> 
                            <button id="btngeneratePassword" type="create" class="btn btn-primary align-self-end">Generated Password</button> 
                        </div>
                                   
                    </div>

                    <div class="form-block">
                        <label for="name">Role</label>
                        <select  class="capitalized" name="role" > 
                            <option value="0" {{ old('role')  == 0 ? 'selected' : '' }}>Employee</option>  
                            <option value="1" {{ old('role') == 1 ? 'selected' : '' }}>Adminstrator</option>                                                                              
                        </select>                  
                    </div>

            <div class="flex justify-content-end gap10">
                        <button  type="create" class="btn btn-primary">Save</button>
                      
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>



@endsection