@extends('layout.front.app')

@section('content')
    
        <div class="container">
            <div class="flex account mt-3 mb-3">  
                <div class="col1">
                        @include('layout.front.sidebar')
                </div>
                <div class="col2">
                    <div class="card no-border pad-2 bg-grey">
                        <div class="card-heading">
                            <h2>Upload Image</h2>                
                        </div>
                        @if (session('success'))
                        <div class="alert alert-success alert-bordered mt-1 mb-1">{{ session('success')}}</div>
                        @endif
                        @if (session('error'))
                        <div class="alert alert-danger alert-bordered mt-1 mb-1">{{ session('error')}}</div>
                        @endif
                        <div class="upload-wrapper mt-2">
                           
                    
                           <div class="upload-container">
                                <div id="avatarContainer" class="uploader align-items">
                                   
                                </div>
                           </div>
    
                           <div class="align-items">
                                <form  id="form-upload"  action="{{ route('upload.avatar')}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="input-file-container">  
                                        <input class="input-file" id="file" type="file" name="avatar" accept="image/jpeg, image/png" >
                                        <label tabindex="0" for="my-file" class="input-file-trigger">Select image</label>
                                    </div>
                                    <button class="btn btn-primary w-200 mt-1">Upload</button>
                                </form>
                           </div>
                           </form>
                          
                        </div>
                    </div>
                </div>   
            </div>
        </div>
    


        
    
   @endsection