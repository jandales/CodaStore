@extends('layout.front.app')
@section('content')
    <div class="container">
        <div class="flex account mt-3 mb-3"> 
            <div class="col1">
                    @include('layout.front.sidebar')
            </div>
            
            <div class="col2">
                <div class="card parent-card no-border  bg-grey">
                    <div class="card-heading">
                            <h2>Profile</h2>
                    </div>    
                    <div class="flex sm-flex-column">                           
                        <div class="profile-wrapper">
                            <div class="avatar">
                                    <img src="{{ auth()->user()->imagePath }}" alt="">   
                                    <a href="{{ route('account.upload') }}"><span><i class="fa fa-camera" aria-hidden="true"></i></span></a>                     
                            </div>    
                            <div class="profile-menu">
                                    <a class="button p-15 dark" href="{{ route('account.edit') }}">Edit Profile</span></a>
                                    <a class="button p-15 dark" href="{{ route('account.password') }}">Change Password</a>
                            </div> 
                        </div> 
                        <div class="profile-info-wrapper">
                           <div class="flex wrap">
                                <div class="profile-info">
                                    <label class="title">Name</label>
                                    <label for="name">{{ auth()->user()->name }}</label>
                                </div> 
    
                                <div class="profile-info ">
                                    <label class="title">Email</label>
                                    <label for="name">{{ auth()->user()->email }}</label>
                                </div> 
    
                                <div class="profile-info">
                                    <label class="title">Mobile Number</label>
                                    <label for="name">{{ auth()->user()->contact }}</label>
                                </div> 
    
                                <div class="profile-info">
                                    <label class="title">Birth Date</label>
                                    <label for="name">{{  auth()->user()->dateofbirth }}</label>
                                </div> 
    
                                <div class="profile-info">
                                    <label class="title">Age</label>
                                    <label for="name">{{ auth()->user()->age }}</label>
                                </div> 
                            </div>    
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    </div> 
@endsection