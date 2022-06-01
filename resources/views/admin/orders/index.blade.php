@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Orders</h1>
   
</div>


<div class="row" >
    <div class="panel-table m-t-2 w-12">
        <div class="panel-header">             
             @if(session('success'))
              <br>
                  <div class="alert alert-success">{{ session('success') }}</div>    
             @endif

             @if(session('error'))
                <br>
                <div class="alert alert-success">{{ session('error') }}</div>
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
                    <li class="{{ (request()->is('admin/orders/pending')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['pending'])}}">Pending</a></li>              
                    <li class="{{ (request()->is('admin/orders/shipped')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['shipped'])}}">Shipped</a></li>
                    <li class="{{ (request()->is('admin/orders/delivered')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['delivered'])}}">Deliver</a></li>
                    <li class="{{ (request()->is('admin/orders/cancelled')) ? 'selected' : '' }}"><a href="{{ route('admin.orders.list',['cancelled'])}}">Cancelled</a></li>
                </ul> 
                <form id="formSearch" action="{{route('admin.orders.search')}}" method="get">               
                    <div class="search-input">
                        <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                        <input type="text" name="keyword"  placeholder="Search here">
                        <a href="{{ route('admin.orders') }}" class="{{$keyword ?? 'hidden' }}"><span class="icon-right"><i class="fa fa-times"></i></span></a>
                    </div>
                </form>
          
        </div>
            <table class="table">
                <thead>
                    <tr>
                        <th class="tr-checkbox">                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                             </div>
                         </th>
                        <th>Person</th>                                                    
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @if ( $orders->count() == 0 )
                        <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
                    @endif 
                    @forEach($orders as $order)
                        <tr>
                            <td class="tr-checkbox"> 
                                <div class="checkbox">   
                                    <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $order->encryptedId() }}">
                                </div>
                            </td>                        
                            <td>
                                <a href="{{ route('admin.orders.show',[$order->encryptedId()])}}">
                                    <div class="flex items-center gap10">
                                        <div class="avatar-sm">
                                            <img src="{{ $order->user->avatar() }}" alt="" srcset="">
                                        </div>
                                        <span>{{ $order->user->name }}</span>
                                     </div>
                                </a>
                            </td>
                            <td>{{ $order->totalItems() . " items" }}</td>
                            <td>@money($order->total())</td>
                            <td>{{  $order->createdAtDate() }}</td>
                            <td><span class="status capitalized {{ $order->statusColor() }}">{{ $order->status }}</span></td>
                            <td width="100px"> 
                                <div class="table-action">
                                    <ul>  
                                        <li>                          
                                            <a href="{{ route('admin.orders.show',[$order->encryptedId()])}}">
                                                <span class="span">
                                                    <i class="fas fa-eye"></i>  
                                                </span>                                                                           
                                            </a>
                                        </li>  
                                            <li>
                                                <a href="#" id="delete">
                                                    <span link ="#" class="span delete">
                                                        <i class="fas fa-trash"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>
                                    </ul>
                                </div>
                            </td>

                        </tr>
                    @endforeach  
                  
                </tbody>
            </table>
         
        
        
            <div class="mt-2 mb-2 right mr10">
                {{ $orders->links() }}
            </div>
            
     
    </div>

   
</div>





@endsection