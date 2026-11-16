@extends('layout.front.app')

@section('title', 'About | Coda')

@section('content')
    <section class="page-hero">
        <div class="container page-hero__inner">
            <span class="eyebrow">About Coda</span>
            <h1 class="page-hero__title">Fewer, better clothes.</h1>
            <p class="page-hero__sub">We make everyday essentials in natural fabrics, designed to be worn for years rather than seasons.</p>
        </div>
    </section>

    <div class="container story">
        <section class="story-row">
            <div class="story-row__text">
                <span class="eyebrow">Our story</span>
                <h2>Started with one good shirt</h2>
                <p>Coda began with a simple frustration: it was hard to find well-made basics that didn't cost a fortune or fall apart after a few washes. So we started small, with a single cotton shirt we wanted to wear every day.</p>
                <p>Today the collection has grown, but the idea hasn't changed. Every piece starts with the fabric, a clean cut and the question of whether we'd still reach for it in five years.</p>
            </div>
            <div class="story-row__media">
                <img src="/img/front/category-4.jpg" alt="Model in a light grey hoodie" loading="lazy">
            </div>
        </section>

        <section class="story-row story-row--reverse">
            <div class="story-row__text">
                <span class="eyebrow">What we care about</span>
                <h2>Made to last, made to wear</h2>
                <ul class="story-list" role="list">
                    <li><strong>Natural fabrics.</strong> Cotton, linen and wool that breathe and soften with time.</li>
                    <li><strong>Simple design.</strong> Quiet colours and clean lines that work with everything you own.</li>
                    <li><strong>Fair pricing.</strong> No seasonal mark-ups, so the price is honest all year round.</li>
                </ul>
                <a href="{{ route('shop') }}" class="btn btn-dark">Shop the collection</a>
            </div>
            <div class="story-row__media">
                <img src="/img/front/category-5.jpg" alt="Close-up of white sneakers and striped track pants" loading="lazy">
            </div>
        </section>
    </div>
@endsection
