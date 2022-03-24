@extends('layout.front.app')
@section('content')   

    <div class="container">
        <div class="flex account mt-3 mb-3"> 
            <div class="col1">
                    @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card parent-card no-border  bg-grey">
                    <div class="card-heading">
                        <h2>Dashboard</h2>
                    </div>
                    <div class="card-body">
                        <div class="card-wrapper">
                            <div class="card">
                                <div class="card-num">
                                    <label for="">39k</label>
                                </div>
                                <div class="card-title bg-teal">
                                    <span class="cwhite"><i class="fas fa-box"></i></span>
                                    <label class="cwhite" for="Orders">Total Cost</label>                                    
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-num">
                                    <label for="">0</label>
                                </div>
                                <div class="card-title bg-warning">
                                    <span class="cwhite"><i class="fas fa-box"></i></span>
                                    <label class="cwhite" for="Orders">Orders</label>                                    
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-num">
                                    <label for="">0</label>
                                </div>
                                <div class="card-title bg-orange">
                                    <span class="cwhite"><i class="fas fa-box"></i></span>
                                    <label class="cwhite" for="Orders">Return</label>                                    
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <h4>Your Default Address</h4>
                        </div>
                    </div>
                </div>    
            </div>
        </div> 
    </div>
   
@endsection