@extends('layout.admin')
@section('content') 

<div class="page-title">
    <h1>Social Site</h1>
</div>
    
<div class="setting-content m-t-2">    
    <div class="panel">            
        <div class="panel-body">
            @foreach ($sites as $site)
            <form id="form" method="POST"  action="{{route('admin.setting.social.update',[$site])}}">
                @csrf 
                @method('put')
                <div class="flex items-center gap20">
                    <div class="form-block w-3">
                        <label for="name" class="capitalized">Name</label>
                         <input type="text" name="name" value="{{ $site->name }}"> 
                    </div>
                    <div class="form-block w-3">
                        <label for="name"  class="capitalized">Url</label>
                        <input type="text" name="url" value="{{ $site->url }}"> 
                    </div>
                    <div class="form-block w-3">
                        <label for="name" class="capitalized block">Code</label>
                        <input type="text" name="code" value="{{ $site->code }}"> 
                    </div>
                    <div class="flex items-center gap20">
                        <button class="btn btn-primary">Update</button>
                    </div>                   
                        <button class="btn btn-danger delete-site" data-url="{{ route('admin.setting.social.destroy',[$site])}}">Delete</button>
                                                                      
                </div>
            </form>
            @endforeach
            <button id="btn-add-site" class="btn btn-primary">Add new Site</button>
            <form id="form-add" method="POST" class="hidden"  action="{{route('admin.setting.social.store')}}">
                @csrf                 
                <div class="flex items-center gap20">
                    <div class="form-block w-3">
                        <label for="name" class="capitalized">Name</label>
                         <input type="text" name="name" value="{{ old('name') }}"> 
                    </div>
                    <div class="form-block w-3">
                        <label for="name"  class="capitalized">Url</label>
                        <input type="text" name="url" value="{{ old('url') }}"> 
                    </div>
                    <div class="form-block w-3">
                        <label for="name" class="capitalized block">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}"> 
                    </div>
                    <div class="flex items-center gap20">
                        <button class="btn btn-primary">Create</button>
                    </div>         
                    <button id="btn-hide-form" class="btn btn-danger">Remove</button>                                         
                </div>
             
            </form>
        </div>
    </div>

    <form id="form-delete" action="" method="POST">
        @csrf
        @method('delete')
    </form>


</div>

<script>
    const btnaddsite = document.getElementById('btn-add-site');
    const btnhideform = document.getElementById('btn-hide-form');
    const btndeleteSites = document.querySelectorAll('.delete-site');
    const form = document.getElementById('form-add');
    const formDelete = document.getElementById('form-delete');
    btnaddsite.onclick = function() {
        btnaddsite.classList.add('hidden')       
        form.classList.remove('hidden');
    }

    btnhideform.onclick = function(e) {
        e.preventDefault();
        form.classList.add('hidden');
        btnaddsite.classList.remove('hidden');    
    }

    btndeleteSites.forEach(button  => {
        button.onclick = function(e) {
            e.preventDefault();
            url =  button.getAttribute('data-url');
            formDelete.setAttribute('action', url);      
            formDelete.submit();
        }       
    })

    


</script>





@endsection