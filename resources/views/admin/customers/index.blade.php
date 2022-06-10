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
                <span id="customer-selected-destroy" data-url={{route('admin.customers.selected.destroy')}} class="btn btn-light"><i class="fas fa-trash"></i></span>
                <span id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 

            </div>      
        </div> 
        <div class="toolbar default-toolbar"> 
                <form id="formSearch" action="{{ route('admin.customers.search') }}" method="get">            
                    <div class="search-input"> 
                        <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                        <input type="text" placeholder="Search" name="keyword" value ="{{ $keyword ?? ''}}">
                        <a href="{{ route('admin.customers') }}" class="{{$keyword ?? 'hidden' }}"><span class="icon-right"><i class="fa fa-times"></i></span></a>
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
            <form id="destroy-customer"  method="post">
                @csrf  
                @method('delete')           
                @if ( count($users) == 0 )
                    <tr> <td colspan="6" ><label class="text-center">No found Record</label></td> </tr>
                @endif
                @foreach ($users as $user)
                    <tr>
                        <td class="column-1">
                            <div class="checkbox">
                                <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $user->encryptedId() }}">
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap10">
                                <div class="avatar-sm">
                                    <img src="{{ $user->imagePath }}" alt="" srcset="">
                                </div>
                               <a href="{{ route('admin.customers.show',[$user->encryptedId()]) }}"><span>{{ $user->name }}</span></a>
                                </div>
                        </td>                        
                        <td>{{$user->email}}</td> 
                        <td><p>{{ $user->contact }}</p></td>                             
                        <td><p>{{ $user->createdAtDate()}}</p></td>
                        <td class="column-action"> 
                            <div class="table-action">
                                <ul>  
                                    <li>                          
                                        <a href="{{ route('admin.customers.show',[$user->encryptedId()]) }}">
                                            <span class="span">
                                                <i class="fas fa-eye"></i>  
                                            </span>                                                                           
                                        </a>
                                    </li>  
                                    @can('delete', $user)
                                        <li>
                                            <a href="#" id="delete">
                                                <span data-url ="{{ route('admin.customers.destroy', [$user->encryptedId()]) }}" class="span destroy-customer">
                                                    <i class="fas fa-trash"></i>  
                                                </span>                                                                           
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>           
                @endforeach                 
            </form>
        </tbody>
    </table>
        
         
            <div class="mt-2 mb-2 right mr10">
                {{ $users->links() }}
            </div>
        
        
    </div>

   
</div>





@endsection