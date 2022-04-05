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
                    <div class="w-3">
                        
                        <div class="mt-1">
                            <span><i class="fa fa-shield"></i> Your credit card details are protected.</span>                    
                        </div>
                        <div class="flex space-between mt-2">
                            <h2>Card Detail</h2>
                            <div class="flex gap5">
                                <span><i class="fab fa-cc-visa"></i></span>
                                <span><i class="fab fa-cc-mastercard"></i></span>
                                <span><i class="fab fa-cc-amex"></i></span>
                            </div>
                        </div>                        
                        <form action="{{ route('account.payment-option.update',[$option]) }}" method="post" class="mt-1">
                            @csrf
                            @method('put')
                            <div class="form-block">
                                <label>Name</label>
                                <input type="text"   name="card_name" value="{{ $option->card_name }}">
                                @error('card_name')
                                    <small class="link-danger">{{ $message }}</small>
                                @enderror
                            </div>
                
                            <div class="form-block">
                                <label>Card Number</label>
                                <input type="number"   name="card_number" value="{{ $option->card_number }}">
                                @error('card_number')
                                    <small class="link-danger">{{ $message }}</small>
                                @enderror
                            </div>
                
                            <div class="flex gap10">
                                <div class="form-block w-9">
                                    <label>Expired Date</label>
                                    <input type="text" name="card_expire_date" value="{{ $option->card_expire_date }}">
                                    @error('card_expire_date')
                                        <small class="link-danger">{{ $message }}</small>
                                    @enderror
                                    @error('card_cvc')
                                        <small class="link-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                
                                <div class="form-block w-3">
                                    <label>CVC</label>
                                    <input type="text"  name="card_cvc" value="{{ $option->card_cvc }}">                                  
                                </div>
                            </div>
                           
                          
                            <span class="text-md mt-1">PHP1.00 will be deducted as verification fee. It will be refunded to your card within 14 days.</span>
                
                            <div class="flex justify-content-flex-end gap10 mt-2">
                                <button class="btn btn-light w-3">Cancel</button>
                                <button id="btn-card-submit" class="btn btn-dark w-3">Submit</button>    
                            </div>
                           
                
                        </form>
                    </div>
                </div>
             
            </div>

        </div>
       
    </div>

   


@endsection



 
