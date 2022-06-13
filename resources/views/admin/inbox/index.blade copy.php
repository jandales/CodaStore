@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Inbox</h1>  
</div>

<div class="row" >
    <div class="panel-table m-t-2 w-12">
        <div class="panel-header">             
            @if(session('success'))
                <br><div class="alert alert-success">
                  <label for="">  {{ session('success') }}</label>
                  <span class="remove-alert"><i class="fa fa-times"></i></span>
                </div>    
            @endif
            @if(session('error'))
                <br><div class="alert alert-success">             
                    <label for="">  {{ session('error') }}</label>
                    <span class="remove-alert"><i class="fa fa-times"></i></span>
                </div>
            @endif 
        </div>
 
        <div class="toolbar justify-content-space-between action-toolbar hidden">               
            <label class="title selected-items">2 item Selected</label>
            <div class="btn-action">      
                <span id="deleteSelected" link = {{ route('admin.users.destroySelectedItem') }} class="btn btn-light"><i class="fas fa-trash"></i></span>
                <span  id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 
            </div>      
        </div> 
        <div class="toolbar default-toolbar space-between">
                <ul class="order-navigation">
                    <li class="{{ (request()->is('admin/orders')) ? 'selected' : '' }}"><a href="{{ route('admin.orders')}}">All</a></li>
                    <li class="{{ (request()->is('admin/orders/confirmed')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['confirmed'])}}">To Ship</a></li>              
                    <li class="{{ (request()->is('admin/orders/shipped')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['shipped'])}}">To Recieve</a></li>
                    <li class="{{ (request()->is('admin/orders/completed')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['completed'])}}">Completed</a></li>
                    <li class="{{ (request()->is('admin/orders/cancelled')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['cancelled'])}}">Cancelled</a></li>
                </ul> 
                {{-- <form id="formSearch" action="{{route('admin.orders.search')}}" method="get">               
                    <div class="search-input">
                        <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                        <input type="text" name="keyword"  placeholder="Search here">
                        <a href="{{ route('admin.orders') }}" class="{{$keyword ?? 'hidden' }}"><span class="icon-right"><i class="fa fa-times"></i></span></a>
                    </div>
                </form> --}}
          
        </div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="tr-checkbox">                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                             </div>
                         </th>
                        <th>From</th>                                                                     
                        <th>Subject</th>
                        <th>Sent Date</th>                                     
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <form id="form"  method="post">
                        @csrf   
                        <input name="_method" type="hidden" value="post"> 
                    @if ( $messages->count() == 0 )
                        <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
                    @endif 
                    @forEach($messages as $message)
                        <tr>
                            <td class="tr-checkbox"> 
                                <div class="checkbox">   
                                    <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $message['id'] }}">
                                </div>
                            </td>                        
                            <td>{{ $message['from_email'] }}</td> 
                            <td>{{ $message['subject'] }}</td> 
                            <td>{{ $message['sent_at'] }}</td>  
                            <td width="100px"> 
                                <div class="table-action">
                                    <ul>  
                                        <li>                          
                                            <a href="{{route('admin.inbox.show',[$message['id']])}}">
                                                <span class="span">
                                                    <i class="fas fa-eye"></i>  
                                                </span>                                                                           
                                            </a>
                                        </li>  
                                   
                                            <li>                                              
                                                <a href="#" id="delete">
                                                    <span data-url = ""  class="span delete">
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
         
        
        
            <div class="mt-2 mb-2 right mr10">
             
            </div>
            
     
    </div>

   
</div>






@endsection