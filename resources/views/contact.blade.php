@extends('layout.front.app')

@section('title', 'Contact | Coda')
@section('meta_description', 'Questions about an order, sizing or returns? Get in touch with the Coda team.')

@section('content')
    <section class="page-hero">
        <div class="container page-hero__inner">
            <span class="eyebrow">Contact</span>
            <h1 class="page-hero__title">We're here to help.</h1>
            <p class="page-hero__sub">Questions about an order, sizing or returns? Send us a message and we'll reply within one business day.</p>
        </div>
    </section>

    <div class="container contact-layout">
        <div class="contact-card">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            <h2 class="contact-card__title">Send us a message</h2>
            <form action="{{ route('sendMessage') }}" method="POST" novalidate>
                @csrf
                <div class="form-block">
                    <label for="contact-email">Email</label>
                    <input id="contact-email" type="email" name="email" autocomplete="email" value="{{ old('email') }}" placeholder="you@example.com">
                    @error('email')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-block">
                    <label for="contact-message">Message</label>
                    <textarea id="contact-message" name="content" rows="7" placeholder="How can we help?">{{ old('content') }}</textarea>
                    @error('content')
                        <small class="error-message">{{ $message }}</small>
                    @enderror
                </div>
                <button class="btn btn-dark">Send message</button>
            </form>
        </div>

        <aside class="contact-info" aria-label="Other ways to reach us">
            <div>
                <h2>Orders and returns</h2>
                <p>Track your order or start a return from <a href="{{ route('account.orders', ['all']) }}" class="link-underline">your account</a>.</p>
            </div>
            <div>
                <h2>Response time</h2>
                <p>Monday to Friday, 9am to 6pm. We usually reply within a day.</p>
            </div>
        </aside>
    </div>
@endsection
