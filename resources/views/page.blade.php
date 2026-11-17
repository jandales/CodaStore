@extends('layout.front.app')

@section('title', $page->title . ' | Coda')
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($page->html()))), 155))
@if ($preview)
    @section('noindex', '1')
@endif

@section('content')
    <div class="container content-page">
        @if ($preview)
            <div class="review-note mb-2" role="status"><span>Preview: this page is a draft and customers can't see it yet. Publish it in the admin under Pages.</span></div>
        @endif
        <header class="content-page__head">
            <h1>{{ $page->title }}</h1>
            <p class="content-page__updated">Last updated {{ $page->updated_at->format('F j, Y') }}</p>
        </header>
        <article class="prose">
            {!! $page->html() !!}
        </article>
    </div>
@endsection
