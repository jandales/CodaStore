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
                        <h2>Edit address</h2>
                      
                    </div>
                    <div class="form mt-2 w-3">
                        <form action="{{ route('addressbook.update',[$addressbook])}}" method="post">
                            @csrf
                            @method('put')
                            <div class="flex mb-1">
                                <div class="flex align-items-center justify-content-flex-start">
                                    <input type="radio" name="type" value="0" {!! $addressbook->type == 0 ? "checked" : "" !!} > 
                                    <label for=""  class="m-l-5">Home Address</label>
                                </div>

                                <div class="flex align-items-center justify-content-flex-start ml-1">
                                    <input type="radio" name="type" value="1" {!! $addressbook->type == 1 ? "checked" : "" !!}> 
                                    <label for=""  class="m-l-5">Bussiness Address</label>
                                </div>
                           </div>
                            @error('type')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="form-block">
                                <label>Reciept Name</label>
                                <input type="text" class="user-name"  name="reciept_name" value="{{ $addressbook->reciept_name }}">
                            </div>

                            @error('reciept_name')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                           
                            <div class="form-block">
                                <label>Phone Number</label>
                                <input type="number" name="reciept_number" value="{{ $addressbook->reciept_number }}">
                            </div>

                            @error('reciept_number')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror                           
             

                            <div class="form-block">
                                <label>Street</label>
                                <input type="text" name="street" value="{{ $addressbook->street }}">
                            </div>
                            
                            <div class="form-block">
                                <label>Barangay</label>
                                <input type="text" name="barangay" value="{{ $addressbook->barangay }}">
                            </div>  

                            @error('barangay')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror


                            <div class="form-block">
                                <label>City / Municipality</label>
                                <input type="text" name="city_municipality" value="{{ $addressbook->city_municipality }}">
                            </div> 

                            @error('city_municipality')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div class="form-block">
                                <label>Province</label>
                                <input type="text" name="province" value="{{ $addressbook->province }}">
                            </div> 

                            @error('province')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                          
                            <div class="form-block">
                                    <button class="btn btn-primary user-btn">Update</button>                              
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>  
@endsection