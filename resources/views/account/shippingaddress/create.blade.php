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
                            <h2>Create address book</h2>                        
                        </div>
                     
                        <div class="form mt-2 w-6 sm-w-12">
                            <form action="{{ route('account.shippingaddress.store')}}" method="post">
                                @csrf                         
                         

                                <div class="form-block">
                                    <label>First name</label>
                                    <input type="text" class="user-name"  name="firstname" value="{{ old('firstname')}}">
                                </div>

                                @error('firstname')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Lastname</label>
                                    <input type="text" class="user-name"  name="lastname" value="{{ old('lastname')}}">
                                </div>

                                @error('lastname')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                    <label>Phone Number</label>
                                    <input type="number" name="phone" value="{{ old('phone')}}">
                                </div>

                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-block">
                                    <label>Street</label>
                                    <input type="text" name="street" value="{{ old('street') }}">
                                </div>
                                
                                <div class="form-block">
                                    <label>City</label>
                                    <input type="text" name="city" value="{{ old('city') }}">
                                </div>  

                                @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror


                                <div class="form-block">
                                    <label>Country</label>
                                    <input type="text" name="country" value="{{ old('country') }}">
                                </div> 

                                @error('country')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Region</label>
                                    <input type="text" name="region" value="{{ old('region') }}">
                                </div>

                                @error('region')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-block">
                                    <label>Zip Code</label>
                                    <input type="text" name="zipcode" value="{{ old('zipcode') }}">
                                </div>

                                @error('zipcode')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                        <button class="btn btn-dark w-12">CREATE</button>                              
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@endsection