@extends('layout.admin')

@section('title', ($page->exists ? $page->title : 'New page') . ' | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>{{ $page->exists ? $page->title : 'New page' }}</h1>
    <div class="order-doc-actions">
        @if ($page->exists)
            <a href="{{ $page->url() }}" class="btn btn-light" target="_blank" rel="noopener"><i class="fas fa-eye" aria-hidden="true"></i> {{ $page->published ? 'View' : 'Preview' }}</a>
        @endif
        <a href="{{ route('admin.pages') }}" class="btn btn-light">All pages</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<form method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="panel page-form">
    @csrf
    @if ($page->exists) @method('put') @endif
    <div class="panel-body">
        <div class="form-block">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" maxlength="120" required value="{{ old('title', $page->title) }}">
        </div>
        <div class="form-block">
            <label for="slug">Web address</label>
            <div class="slug-input"><span>/pages/</span><input type="text" id="slug" name="slug" maxlength="80" value="{{ old('slug', $page->slug) }}" placeholder="made from the title"></div>
        </div>
        <div class="form-block">
            <label for="body">Text</label>
            <textarea id="body" name="body" rows="22" required aria-describedby="body-help">{{ old('body', $page->body) }}</textarea>
            <p id="body-help" class="form-help">Write in Markdown: <code>## Heading</code>, <code>**bold**</code>, <code>- list item</code>, <code>[link text](https://...)</code>. HTML is removed.</p>
        </div>
        <div class="form-block">
            <label for="meta_description">Search engine description <span class="label-hint">(optional, up to 160 characters)</span></label>
            <input type="text" id="meta_description" name="meta_description" maxlength="160" value="{{ old('meta_description', $page->meta_description) }}">
        </div>
        <div class="form-block">
            <label for="footer_group">Show in</label>
            <select id="footer_group" name="footer_group">
                @foreach (\App\Models\Page::FOOTER_GROUPS as $key => $label)
                    <option value="{{ $key }}" {{ old('footer_group', $page->footer_group) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <label class="sale-toggle"><input type="checkbox" name="published" value="1" {{ old('published', $page->published) ? 'checked' : '' }}> Published (customers can see this page)</label>
        <div class="flex gap10 mt20">
            <button class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@if ($page->exists)
    <form method="post" action="{{ route('admin.pages.destroy', $page) }}" class="mt20" onsubmit="return confirm('Delete this page? This cannot be undone.')">
        @csrf
        @method('delete')
        <button class="btn btn-danger">Delete page</button>
    </form>
@endif
@endsection
