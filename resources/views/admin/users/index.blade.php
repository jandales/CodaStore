@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Users</h1>
   
</div>

@if(session('success'))
<div class="alert alert-success mt20">{{ session('success') }}</div>                
@endif 
@if(session('error'))
<div class="alert alert-warning mt20">{{ session('error') }}</div>                
@endif 

<div class="row" >

    <div class="panel-table m-t-1 w-12">
        <div class="panel-header"> 
          
        </div>
        <div class="toolbar justify-content-space-between action-toolbar hidden"> 
          
              
                <label class="title selected-items">2 item Selected</label>
                <div class="btn-action"> 
                    <form id="action-form" action="{{route('admin.users.updateSelectItemRoleTo')}}" method="post">
                        @csrf
                        @method('put')                      
                        <div class="select-option">
                            <select name="role" id="">
                                <option value="">Change role to..</option>
                                <option value="0">Employee</option>
                                <option value="1">Adminstrator</option>
                            </select>
                            <span id="update-selected-item-role-to" class="btn btn-light">Change</span>
                        </div>  
                    </form> 
                    <span class="btn btn-light">Send Password Reset</span>                                
                    <span id="deleteSelected" link = {{ route('admin.users.destroySelectedItem') }} class="btn btn-light"><i class="fas fa-trash"></i></span>
                    <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 

                </div>
          
        </div> 
        <div class="toolbar justify-content-space-between  default-toolbar"> 
                <form id="formSearch" action="{{ route('admin.users.search') }}" method="post">
                    @csrf
                    <div class="search-input"> 
                        <span class="icon-left"></span>                           
                        <input type="text" placeholder="Search" name="search">
                        <span class="icon-right" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>
                    </div>                     
                </form>
               

                <a href="{{route('admin.users.create')}}" class="btn btn-primary mr-2">                   
                     Add new user
                </a>
        </div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="column-1">                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox">                             
                             </div>
                         </th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>                                        
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <form id="form"  method="post">
                        @csrf   
                        <input name="_method" type="hidden" value="post">              
                        @foreach ($users as $user) 
                            <tr>
                                <td class="column-1">
                                    <div class="checkbox">
                                            <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $user->id }}">
                                    </div>                                
                                </td>
                                <td>
                                    <div class="flex items-center gap10">
                                        <div class="avatar-sm">
                                            <img src="/{{ $user->avatar() }}" alt="" srcset="">
                                        </div>
                                        <span>{{ $user->username }}</span>
                                    </div>
                                   
                                </td>
                                <td> {{ $user->fullName() }} </td>                         
                                <td>{{$user->email}}</td> 
                                <td><p>{{ $user->userRole() }}</p></td>
                                <td width="100px"> 
                                    <div class="table-action">
                                        <ul>  
                                            <li>                          
                                                <a href="{{ route('admin.users.edit',[$user]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-pen"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                            <li>                          
                                                <a href="{{ route('admin.users.show',[$user]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-eye"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>    
                                            <li>
                                                <a href="#" id="delete">
                                                    <span link ="{{ route('admin.users.destroy', [$user]) }}" class="span delete">
                                                        <i class="fas fa-trash"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>   
                        @endforeach
                    </form>
                </tbody>
            </table>    
    </div>

   
</div>

<script type="module">

    checkBoxProperties.selecteCount = 0;

    function submitForm(route, method){
        const form = document.getElementById('form');
        const input = document.querySelector('input[name="_method"]');
        if(method != null) input.value = method; 
        form.setAttribute('action', route); 
        form.submit();       
    } 

    document.querySelectorAll('.delete').forEach(element => {
        element.addEventListener('click', event => {
            const route = element.getAttribute('link');
            submitForm(route, 'delete');
        })
    });

    document.querySelector('#deleteSelected').addEventListener('click', function()  {
        submitForm(this.getAttribute('link'), 'delete')
    })

    document.querySelector('#update-selected-item-role-to').addEventListener('click', function() {
        const form = document.getElementById('action-form');
      
        const list = [];
        document.querySelectorAll('.childCheckbox').forEach(element => {           
            element.type = 'hidden'
            form.append(element)  
        });
        form.submit();
    })

    
</script>


@endsection