@extends('layout.front.app')
@section('content')   
    <div class="container">
        <div class="flex account mt-3 mb-3"> 
            <div class="col1">
                @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card no-border pad-2 bg-grey min-height-400">
                    <div class="card-heading">
                        <h2>Shipping Address</h2>
                        <a  href="{{ route('account.shippingaddress.create') }}" class="btn btn-dark">Add address</a>
                    </div>
                    <div class="address-wrapper">
                        @if(session('success'))
                            <div class="alert alert-success mt-1 w-12">{{ session('success') }}</div>
                        @endif
                        @if(session('danger'))
                             <div class="alert alert-warning mt-1 w-12">{{ session('danger') }}</div>
                        @endif  
                        <table class="table table-address bordered bg-white p-20">
                            <thead>
                                    <tr>                                                                        
                                        <th>Name</th>
                                        <th>Phone Number</th>
                                        <th>Address</th>
                                        <th>Country</th>                                      
                                        <th>Action</th>
                                    </tr>
                            </thead>
                            <tbody>                                     
                                @foreach ($shippingaddress as $item) 
                                    <tr class = "bg-grey-hover {{ $item->status == 1 ? 'address-default' : '' }} ">                                       
                                        <td>{{ $item->firstname . " " . $item->lastname }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->street . " " . $item->city . " " . $item->region }}</td>
                                        <td>{{ $item->country }}</td>                                           
                                        <td>
                                            <ul class="address-ul">
                                                <li>                                                   
                                                    <form  action="{{ route('account.shippingaddress.update-status',[$item->encryptedId()]) }}"  method="post">
                                                        @csrf @method('put')  
                                                        <button class="borderless">
                                                            <i class="fa-solid  {{ $item->status == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                        </button>                                                  
                                                    </form>
                                                </li>
                                                <li>
                                                    <a href="{{ route('account.shippingaddress.edit',[$item->encryptedId() ]) }}">
                                                        <span class="tbl-action">
                                                            <i class="fas fa-pen"></i>
                                                        </span>
                                                    </a>                                                    
                                                </li>
                                                <li>                                                    
                                                    <form action="{{ route('account.shippingaddress.destroy', [$item->encryptedId()]) }} "  method="post">
                                                        @csrf @method('delete')  
                                                        <button  class="tbl-action">
                                                            <i class="far fa-trash-alt"></i>
                                                        </button>                                                           
                                                    </form>
                                                </li>
                                            </ul>      
                                        </td>
                                    </tr>       
                                @endforeach                                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> 
        </div>
    </div>
@endsection

