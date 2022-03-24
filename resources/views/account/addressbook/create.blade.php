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
                        <div class="form mt-2 w-3">
                            <form action="{{ route('addressbook.store')}}" method="post">
                                @csrf 
                                
                                <div class="flex mb-1">
                                    <div class="flex align-items-center justify-content-flex-start">
                                        <input type="radio" name="type" value="0"> 
                                        <label for=""  class="m-l-5">Home Address</label>
                                    </div>

                                    <div class="flex align-items-center justify-content-flex-start ml-1">
                                        <input type="radio" name="type" value="1"> 
                                        <label for=""  class="m-l-5">Bussiness Address</label>
                                    </div>
                            </div>

                            @error('type')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                                <div class="form-block">
                                    <label>Reciept Name</label>
                                    <input type="text" class="user-name"  name="reciept_name" value="{{ old('reciept_name')}}">
                                </div>

                                @error('reciept_name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Reciept email</label>
                                    <input type="text" class="user-name"  name="reciept_email" value="{{ old('reciept_email')}}">
                                </div>

                                @error('reciept_email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                    <label>Phone Number</label>
                                    <input type="number" name="reciept_number" value="{{ old('reciept_number')}}">
                                </div>

                                @error('reciept_number')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                
                                <div class="form-block">
                                    <label>Street</label>
                                    <input type="text" name="street" value="{{ old('street') }}">
                                </div>
                                
                                <div class="form-block">
                                    <label>Barangay</label>
                                    <input type="text" name="barangay" value="{{ old('barangay') }}">
                                </div>  

                                @error('barangay')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror


                                <div class="form-block">
                                    <label>City / Municipality</label>
                                    <input type="text" name="city_municipality" value="{{ old('city_municipality') }}">
                                </div> 

                                @error('city_municipality')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror

                                <div class="form-block">
                                    <label>Province</label>
                                    <input type="text" name="province" value="{{ old('province') }}">
                                </div>

                                @error('province')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            
                                <div class="form-block">
                                        <button class="btn btn-primary user-btn">Create</button>                              
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@endsection