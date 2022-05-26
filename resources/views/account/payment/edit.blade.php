@extends('layout.front.app')
@section('content')   
    <div class="container">

        <div class="flex account mt-3 mb-3">
            <div class="col1">
                @include('layout.front.sidebar')
            </div>
            <div class="col2">

                <div class="card no-border pad-2 bg-grey">
                    <div class="card-heading">
                        <h2>Edit Card</h2>                        
                    </div>
                    <div class="w-6 sm-w-12">
                        <form action="{{ route('account.payment-option.update',[$option->encryptedId()]) }}" method="post" class="mt-1">
                            @csrf
                            @method('put')
                            <div class="form-block">
                                <label>Card Name</label>
                                <input type="text"   name="card_name" value="{{ $option->card_name }}">
                                @error('card_name')
                                    <small class="alert-text link-danger">{{ $message }}</small>
                                @enderror
                            </div>                
                                  
                            <div class="form-block">
                                <label>Card Data</label>
                                <div class="input-custom">
                                    <div class="inline-form-group">
                                        <input type="text" class="card-number w-6" class=""  name="card_number" value="{{ $option->card_number }}" placeholder="Card Number">
                                        <input type="text"  class="expired-date w-3"  name="card_expire_date" value="{{ $option->card_expire_date }}" placeholder="MM/YY" >
                                        <input type="text"  class="w-3"  name="card_cvc" value="{{ $option->card_cvc }}" placeholder="Code">
                                     </div>
                                   
                                </div>
                                @error('card_number')
                                <small class="alert-text link-danger">{{ $message }}</small>
                                @enderror
                                @error('card_expire_date')
                                    <small class="alert-text link-danger">{{ $message }}</small>
                                @enderror
                                @error('card_cvc')
                                    <small class="alert-text link-danger">{{ $message }}</small>
                                @enderror
                               
                            </div>
                           
                          
                                <button id="btn-card-submit" class="btn btn-dark ">Submit</button>  
                       
                           
                
                        </form>
                    </div>
                </div>
             
            </div>

        </div>
       
    </div>

   


@endsection



 
