@extends('layout.front.app')

@section('title', 'Bag reminders | Coda')

@section('content')
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
                <div class="login-register-header">
                    <h1>Bag reminders are off</h1>
                    <p class="login-register-sub">We won't email you about items left in your bag. Order emails still arrive as usual.</p>
                </div>
                <div class="login-register-body">
                    <a href="{{ route('shop') }}" class="btn btn-dark btn-block">Continue shopping</a>
                </div>
            </div>
        </div>
    </div>
@endsection
