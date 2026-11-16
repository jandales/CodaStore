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
                        <h2>Shipping addresses</h2>
                        <a  href="{{ route('account.shippingaddress.create') }}" class="btn btn-dark">Add address</a>
                    </div>
                    <div class="address-wrapper">
                        @if(session('success'))
                            <div class="alert alert-success mt-1 w-12">{{ session('success') }}</div>
                        @endif
                        @if(session('danger'))
                             <div class="alert alert-warning mt-1 w-12">{{ session('danger') }}</div>
                        @endif  
                        @if ($shippingaddress->count() == 0)
                            <div class="empty-state account-empty">
                                <h2>No saved addresses</h2>
                                <p>Save an address to check out faster next time.</p>
                            </div>
                        @else
                        <table class="table table-address bordered bg-white p-20">
                            <thead>
                                    <tr>                                                                        
                                        <th>Name</th>
                                        <th>Phone number</th>
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
                                                        <button class="borderless" aria-label="Set as default">
                                                            <i class="fa-solid  {{ $item->status == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                        </button>                                                  
                                                    </form>
                                                </li>
                                                <li>
                                                    <a href="{{ route('account.shippingaddress.edit',[$item->encryptedId() ]) }}" aria-label="Edit">
                                                        <span class="tbl-action">
                                                            <i class="fas fa-pen"></i>
                                                        </span>
                                                    </a>                                                    
                                                </li>
                                                <li>                                                    
                                                    <form action="{{ route('account.shippingaddress.destroy', [$item->encryptedId()]) }} "  method="post">
                                                        @csrf @method('delete')  
                                                        <button class="tbl-action" aria-label="Delete">
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
                        @endif
                    </div>
                </div>
            </div> 
        </div>
    </div>
@endsection

