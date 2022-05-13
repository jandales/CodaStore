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
                            <h2>Edit Shipping Adddress</h2>                        
                        </div>
                        
                        <div class="form mt-1 w-6 sm-w-12">
                            <form action="{{ route('account.shippingaddress.update', [$address] ) }}" method="post">
                                @csrf    
                                @method('put')
                                <div class="form-block">
                                    <label>First name</label>
                                    <input type="text" class="user-name"  name="firstname" value="{{ $address->firstname }}"> 
                                </div>

                                @error('firstname')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Lastname</label>
                                    <input type="text" class="user-name"  name="lastname" value="{{ $address->lastname }}">
                                </div>

                                @error('lastname')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                    <label>Phone Number</label>
                                    <input type="number" name="phone" value="{{ $address->phone }}">
                                </div>

                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-block">
                                    <label>Street</label>
                                    <input type="text" name="street" value="{{ $address->street  }}">
                                </div>
                                
                                <div class="form-block">
                                    <label>City</label>
                                    <input type="text" name="city" value="{{ $address->city }}">
                                </div>  

                                @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror


                                <div class="form-block">
                                    <label>Country</label>
                                    <input type="text" name="country" value="{{ $address->country }}">
                                </div> 

                                @error('country')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Region</label>
                                    <input type="text" name="region" value="{{ $address->region }}">
                                </div>

                                @error('region')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-block">
                                    <label>Zip Code</label>
                                    <input type="text" name="zipcode" value="{{ $address->zipcode }}">
                                </div>

                                @error('zipcode')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                        <button class="btn btn-dark w-12">SAVE CHANGES</button>                              
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@endsection