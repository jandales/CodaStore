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
                        <h2>Payment Option</h2>
                        <a href="{{ route('account.payment-option.create') }}"  class="btn btn-primary">Add new Card</a>
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
                                    <th>Card Name</th>
                                    <th>Card Number</th>
                                    <th>Expired Date</th>
                                    <th>CVC</th>                                      
                                    <th>Action</th>
                                </tr>
                        </thead>
                        <tbody>                                     
                            @foreach ($payment_options as $option)                          
                                 <tr class ="bg-grey-hover @if($option->status == 1) address-default  @endif">                                                                     
                                    <td>{{ $option->card_name }}</td>
                                    <td>{{ $option->card_number }}</td>
                                    <td>{{ $option->card_expire_date }}</td>
                                    <td>{{ $option->card_cvc }}</td>                                           
                                    <td>
                                        <ul class="address-ul">
                                            <li>
                                                <form action="{{ route('account.payment-option.update.status', [$option] )}}" method="post">
                                                    @csrf
                                                    @method('put')
                                                    <button class="borderless">
                                                            <i class="fa-solid {{ $option->status == 1 ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>                                                          
                                                    </button>
                                                </form>
                                              
                                            </li>
                                            <li>
                                                <a href="{{ route('account.payment-option.edit',[$option]) }}">
                                                    <span class="tbl-action">
                                                        <i class="fas fa-pen"></i>
                                                    </span>
                                                </a>                                                    
                                            </li>
                                            <li>
                                                <form action="{{ route('account.payment-option.destroy', [$option]) }}"  method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button  class="tbl-action">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                </form>                                               
                                            </li>
                                                <form id="form-default"  method="post">
                                                    @csrf @method('put')                                                    
                                                </form>
                                            
                                                <form id="form-delete"   method="post">
                                                    @csrf @method('delete')                                                             
                                                </form>
                                               
                                           
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



