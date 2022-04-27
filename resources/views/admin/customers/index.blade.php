@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Customers</h1>
   
</div>
     
@if(session('success'))
<br>
    <div class="alert alert-success">{{ session('success') }}</div>    
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
        <div class="toolbar default-toolbar"> 
                <form id="formSearch" action="{{ route('admin.users.search') }}" method="post">
                    @csrf
                    <div class="search-input"> 
                        <span class="icon-left"></span>                           
                        <input type="text" placeholder="Search" name="search">
                        <span class="icon-right" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>
                    </div>                     
                </form>
        </div>
    <table class="table">
        <thead>
            <tr>
                <th class="column-1">                   
                    <div class="checkbox">
                        <input type="checkbox" id="parentCheckbox" name="checkbox" >
                    </div>
                </th>
                <th>Name</th>
                <th>Email</th>                                          
                <th>Phone</th>              
                <th>Date Created</th>
                <th class="column-action"></th>
            </tr>
        </thead>
        <tbody>
            <form id="form"  method="post">
                @csrf  @method('delete')           
                    @if ( count($users) != 0 )
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
                                            <img src="{{ $user->avatar() }}" alt="" srcset="">
                                        </div>
                                        <span>{{ $user->name }}</span>
                                     </div>
                                </td>                        
                                <td>{{$user->email}}</td> 
                                <td><p>{{ $user->contact }}</p></td>                             
                                <td><p>{{ $user->createdAtDate()}}</p></td>
                                <td class="column-action"> 
                                    <div class="table-action">
                                        <ul>  
                                            <li>                          
                                                <a href="{{ route('admin.customers.show',[$user]) }}">
                                                    <span class="span">
                                                        <i class="fas fa-eye"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>  
                                                <li>
                                                    <a href="#" id="delete">
                                                        <span link ="{{ route('admin.users.destroy', [$user->id]) }}" class="span delete">
                                                            <i class="fas fa-trash"></i>  
                                                        </span>                                                                           
                                                    </a>
                                                </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>           
                        @endforeach
                    @else
                            <tr><td>No found Record</td></tr>
                    @endif
            </form>
        </tbody>
    </table>
        
            <form id='destroy' action="{{ route('admin.users.destroy', [$user]) }}" method="post">
                @csrf      
                @method('put')  
            </form>

            <div class="mt-2 mb-2 right mr10">
                {{ $users->links() }}
            </div>
        
        
    </div>

   
</div>


<script>   




function url(route){
    let form =  document.getElementById('form')
    form.setAttribute('action', route)  
    form.submit() 
}

function destroy(route)
{
    let form =  document.getElementById('destroy')
    form.setAttribute('action', route)    
    form.submit() 
}













</script>


@endsection