@extends('layout.admin')

@section('title', 'Pages | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Pages</h1>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> New page</a>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

<div class="panel-table">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Title</th>
                <th scope="col">Address</th>
                <th scope="col">Shown in</th>
                <th scope="col">Status</th>
                <th scope="col">Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pages as $page)
                <tr>
                    <td><a class="link-primary" href="{{ route('admin.pages.edit', $page) }}">{{ $page->title }}</a></td>
                    <td><a href="{{ $page->url() }}" target="_blank" rel="noopener">/pages/{{ $page->slug }}</a></td>
                    <td>{{ \App\Models\Page::FOOTER_GROUPS[$page->footer_group] ?? '—' }}</td>
                    <td><span class="status {{ $page->published ? 'bg-success' : 'bg-warning' }}">{{ $page->published ? 'Published' : 'Draft' }}</span></td>
                    <td>{{ $page->updated_at->format('M j, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
