@extends('layout.admin')

@section('content')
<div class="container">

    <div class="page-title">
        <h1>Customer Info</h1> 
        <a href="{{ route('admin.customers')}}" class="btn btn-danger ml10">Back</a>       
    </div>       
        
<div class="page-container flex-column m-t-2">    
    <div class="w-12">
        <div class="panel">                         
        <div class="panel-body">
            <div class="flex gap50">
                <div class="w-3">
                    <div class="avatar-big center">
                        <img class="round" src="{{ $user->imagePath }}" alt="" srcset="">
                    </div> 
                    <div class="m-t-1 centered flex-column">
                        <h4>{{ $user->name }}</h4>
                        <div class="flex gap50 m-t-2">
                            <div>                             
                                <div class="circle centered bor-success">
                                    {{ $user->completed() }}
                                </div>
                                <label class="txt-center mt10">Completed</label>
                            </div>
                            <div>
                                <div class="circle centered bor-warning">
                                    {{ $user->returned() }}
                                </div>
                                <label class="txt-center mt10">Return</label>
                            </div>
                           
                            <div>
                                <div class="circle centered bor-danger">
                                    {{ $user->cancelled() }}
                                </div>
                                <label class="txt-center mt10">Cancelled</label>
                            </div>
                        </div>
                               
                          
                                
                    </div>    
                </div>
                <div class="w-9">
                    <div class="form-block">
                        <label for="name">Name</label>
                        <input type="text" name="username" value="{{ $user->name }}" disabled>                 
                    </div>
                    <div class="form-block">
                        <label for="name">Email</label>
                        <input type="text" name="username" value="{{ $user->name }}" disabled>                 
                    </div>
                    <div class="form-block">
                        <label for="name">Contact Number</label>
                        <input type="text" name="username" value="{{ $user->contact }}" disabled>                 
                    </div>
                    <div class="form-block">
                        <label for="name">BirthDate</label>
                        <input type="text" name="username" value="{{ $user->dateofbirth }}" disabled>                 
                    </div>
                    <div class="form-block">
                        <label for="name">Age</label>
                        <input type="text" name="username" value="{{ $user->age }}" disabled>                 
                    </div>
                </div> 
            </div>           
        </div>
        </div>
    </div>
    
    <div class="w-12">
        <div class="panel-table">
            <table class="table">
                <thead>
                    <tr>                                             
                        <th class="pad-left-20">Order Number</th>
                        <th>items</th>                                          
                        <th>Total</th>
                        <th>Status</th> 
                        <th></th>                    
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->orders as $order)
                    <tr>                      
                        <td class="pad-left-20">{{ $order->ordernumber() }}</td>                     
                        <td>{{ $order->totalItems() }}</td>
                        <td>@money($order->total())</td>                  
                        <td><p class="status capitalized">{{ $order->status }}</p></td>
                        <td width="100px"> 
                            <div class="table-action">
                                <ul>  
                                    <li>                          
                                        <a href="{{ route('admin.orders.show',[$order])}}">
                                            <span class="span">
                                                <i class="fas fa-eye"></i>  
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
        </div>
    </div>
</div>



        


</div>

@endsection