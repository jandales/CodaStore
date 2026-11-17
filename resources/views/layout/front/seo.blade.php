@php
    // Inline @section values are already escaped by Blade, so they are printed with {!! !!} below.
    $seoTitle = trim($__env->yieldContent('title', 'Coda | Modern clothing store'));
    $seoDescription = trim($__env->yieldContent('meta_description', 'Considered essentials in natural fabrics, cut simply and made to be worn often. Shop shirts, jackets and knitwear at Coda.'));
    $seoImage = trim($__env->yieldContent('meta_image', e(asset('img/front/hero-1800.jpg'))));
    $seoType = trim($__env->yieldContent('og_type', 'website'));

    // Canonical: the page without filters or sorting, keeping the page number.
    $page = (int) request()->query('page', 1);
    $canonical = url()->current() . ($page > 1 ? '?page=' . $page : '');

    // Keep private, transactional and filtered pages out of search results.
    $noindex = $__env->hasSection('noindex')
        || request()->is('cart*', 'checkout*', 'account*', 'orders*', 'search*', 'login', 'register', 'email/*', 'forgot-password*', 'reset-password*', 'newsletter/*')
        || collect(request()->query())->except('page')->isNotEmpty();
@endphp
<meta name="description" content="{!! $seoDescription !!}">
<link rel="canonical" href="{{ $canonical }}">
@if ($noindex)
    <meta name="robots" content="noindex, follow">
@endif
<meta property="og:site_name" content="Coda">
<meta property="og:type" content="{!! $seoType !!}">
<meta property="og:title" content="{!! $seoTitle !!}">
<meta property="og:description" content="{!! $seoDescription !!}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{!! $seoImage !!}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{!! $seoTitle !!}">
<meta name="twitter:description" content="{!! $seoDescription !!}">
<meta name="twitter:image" content="{!! $seoImage !!}">
@stack('head')
