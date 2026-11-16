@extends('layout.front.app')

@section('title', 'Page not found | Coda')

@section('content')
    <div class="container">
        <div class="empty-state error-page">
            <span class="eyebrow">Error 404</span>
            <h1>We couldn't find that page</h1>
            <p>The link may be broken, or the page may have moved.</p>
            <div class="error-page__actions">
                <a href="{{ route('shop') }}" class="btn btn-dark">Shop all products</a>
                <a href="{{ route('home') }}" class="btn btn-default">Go to home</a>
            </div>
        </div>
    </div>
@endsection
