@extends('layout.front.app')

@section('title', 'Newsletter | Coda')

@section('content')
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
                <div class="login-register-header">
                    @if ($done)
                        <h1>You're unsubscribed</h1>
                        <p class="login-register-sub">{{ $subscriber->email }} won't get the Coda newsletter any more. Order emails still arrive as usual.</p>
                    @else
                        <h1>Unsubscribe?</h1>
                        <p class="login-register-sub">Stop sending the Coda newsletter to {{ $subscriber->email }}.</p>
                    @endif
                </div>
                <div class="login-register-body">
                    @if ($done)
                        <a href="{{ route('shop') }}" class="btn btn-dark btn-block">Continue shopping</a>
                    @else
                        <form method="post" action="{{ route('newsletter.unsubscribe.confirm', [$subscriber->token]) }}">
                            @csrf
                            <button class="btn btn-dark btn-block">Unsubscribe</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
