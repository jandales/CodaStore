@extends('checkout')

@section('title', 'Order confirmed | Coda')

@section('content')
    <div class="order-done">
        <span class="order-done__icon" aria-hidden="true"><i class="fas fa-check"></i></span>
        <h1 class="order-done__title">Thank you for your order</h1>
        @if (session('order_number'))
            <p class="order-done__number">Order number <strong>{{ session('order_number') }}</strong></p>
        @endif
        <p class="order-done__text">Your payment was received and your order is confirmed. You can follow it from your account.</p>
        <div class="order-done__actions">
            <a class="btn btn-dark" href="{{ route('account.orders', ['all']) }}">View my orders</a>
            <a class="btn btn-default" href="{{ route('shop') }}">Continue shopping</a>
        </div>
    </div>
@endsection
