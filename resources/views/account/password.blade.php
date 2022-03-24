@extends('layout.front.app')

@section('content')
    
        <div class="container">
            <div class="flex account mt-3 mb-3">          
                <div class="col1">
                    @include('layout.front.sidebar')
                </div>

                <div class="col2">

                    <div class="card no-border pad-2  bg-grey">
                        <div class="card-heading">
                            <h2>Change Password</h2>
                        </div>
                        <div class="form w-3 mt-1">
                             @if (session('success'))
                                <div class="alert alert-success alert-bordered mt-1 mb-1">{{ session('success')}}</div>
                             @endif
    
                             @if (session('error'))
                                 <div class="alert alert-danger alert-bordered mt-1 mb-1">{{ session('error')}}</div>
                             @endif
                             <form   method="POST" action="{{ route('users.changePassword')}}">  
                                @csrf                    
                                <div class="form-block">
                                    <label>New passsword</label>
                                    <input type="password" name="password" class="required">                     
                                </div> 
                                @error('password')
                                   <div class="alert alert-danger">{{ $message }}</div> 
                                @enderror
                                <div class="form-block">
                                    <label>Confirm passsword</label>
                                    <input type="password" name="password_confirmation" class="required">
                                </div> 
                                @error('password_confirmation')
                                    <div class="alert alert-danger">{{ $message }}</div> 
                                @enderror
                                <div class="form-block">
                                    <label>Enter your current password</label>
                                    <input type="password" name="validator">
                                </div> 
                                @error('validator')
                                    <div class="alert alert-danger">{{ $message }}</div> 
                                @enderror
                                
                                <div class="flex">
                                    <button id="submit" class="button p-15 w-6  dark">SAVE</button> 
                                    <a href="{{ url()->previous() }}"  class="btn w-6 p-15  btn-danger ml-1">CANCEL</a>  
                                </div>
                            
                                                       
                            </form> 
                        </div>
                    </div>
                </div>


             



            </div>

         
        </div>
    
  
    
   @endsection