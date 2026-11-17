@extends('layout.admin')

@section('title', 'Import products | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Import products</h1>
    <a href="{{ route('admin.products.import.template') }}" class="btn btn-light"><i class="fas fa-download" aria-hidden="true"></i> Download template</a>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

@if (!isset($report))
    <section class="dash-card import-card" aria-labelledby="upload-title">
        <h2 id="upload-title" class="dash-card__title">Upload a CSV file</h2>
        <p class="dash-card__sub">Nothing changes yet. You'll see what each row would do and confirm before anything is saved.</p>

        <form method="post" action="{{ route('admin.products.import.preview') }}" enctype="multipart/form-data" class="import-form">
            @csrf
            <label for="import-file" class="sr-only">CSV file</label>
            <input type="file" id="import-file" name="file" accept=".csv,text/csv" required>
            @error('file')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror
            <button class="btn btn-primary">Check file</button>
        </form>

        <details class="import-help">
            <summary>How the file should look</summary>
            <ul>
                <li>The first row holds the column names: <strong>Name, SKU, Category, Status, Regular price, Sale price, On sale, Stock, Sizes, Description</strong>. Only Name or SKU is required as a column.</li>
                <li>A row whose SKU matches an existing product <strong>updates</strong> it. Empty cells leave that value unchanged.</li>
                <li>Other rows <strong>create</strong> a product and need a name, a category and a regular price. New products are drafts unless Status says Published, and use a placeholder photo until you add one.</li>
                <li>Sizes are separated with <code>|</code>, for example <code>S|M|L</code>. They replace the product's sizes.</li>
                <li>You can export your products, edit the file in Excel or Google Sheets, and import it back. Up to {{ \App\Services\Products\ProductImporter::MAX_ROWS }} products per file.</li>
            </ul>
        </details>
    </section>
@else
    @php $ready = $report['counts']['create'] + $report['counts']['update']; @endphp
    <section class="dash-card import-card" aria-labelledby="report-title">
        <h2 id="report-title" class="dash-card__title">Check before importing: {{ $filename }}</h2>
        <ul class="import-counts" role="list">
            <li><strong>{{ $report['counts']['create'] }}</strong> new</li>
            <li><strong>{{ $report['counts']['update'] }}</strong> updated</li>
            <li class="{{ $report['counts']['error'] ? 'has-errors' : '' }}"><strong>{{ $report['counts']['error'] }}</strong> with errors</li>
        </ul>
        @if ($report['error'])
            <div class="alert alert-danger" role="alert">{{ $report['error'] }}</div>
        @endif
        @if ($report['ignored'])
            <p class="form-help">These columns aren't used and will be ignored: {{ implode(', ', $report['ignored']) }}.</p>
        @endif

        <div class="import-actions">
            @if ($ready)
                <form method="post" action="{{ route('admin.products.import.run') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button class="btn btn-primary">Import {{ $ready }} {{ \Illuminate\Support\Str::plural('product', $ready) }}</button>
                </form>
            @endif
            <a href="{{ route('admin.products.import') }}" class="btn btn-light">{{ $ready ? 'Cancel' : 'Upload a fixed file' }}</a>
            @if ($ready && $report['counts']['error'])
                <p class="form-help">Rows with errors are skipped. Fix them and import the file again later; rows already imported will just update.</p>
            @endif
        </div>
    </section>

    <div class="panel-table m-t-2">
        <table class="table import-table">
            <thead>
                <tr>
                    <th scope="col">Row</th>
                    <th scope="col">Product</th>
                    <th scope="col">Result</th>
                    <th scope="col">Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr class="{{ $row['errors'] ? 'is-error' : '' }}">
                        <td>{{ $row['line'] }}</td>
                        <td>
                            <span class="capitalize">{{ $row['name'] ?: '—' }}</span>
                            @if ($row['sku'])<small class="import-sku">{{ $row['sku'] }}</small>@endif
                        </td>
                        <td>
                            @if ($row['errors'])
                                <span class="status bg-danger">Error</span>
                            @elseif ($row['action'] === 'create')
                                <span class="status bg-success">New</span>
                            @else
                                <span class="status bg-primary">Update</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['errors'])
                                <ul class="import-errors">@foreach ($row['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul>
                            @else
                                {{ implode(', ', $row['changes']) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
