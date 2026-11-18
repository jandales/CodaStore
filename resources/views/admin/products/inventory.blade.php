@extends('layout.admin')
@section('content')
<div class="page-title">
    <h1>Inventory</h1>
</div>

<div class="panel-table m-t-1">
    <div class="panel-table-header">
        <div class="flex gap20">
            <div class="flex  items-center gap20 w-2">
                <label for="filter">Filter</label>
                <select name="filter" id="filter">
                    @if($keyword != null)
                            <option value="">-- Select -- </option>
                    @endif
                        <option value="all" {{ $filter == 'all' ? 'selected' : ''  }} data-url="{{route('admin.inventory.filter',['all'])}}" >All</option>
                        <option value="low-stock" {{ $filter == 'low-stock' ? 'selected' : '' }} data-url="{{ route('admin.inventory.filter', ['low-stock']) }}">Low stock</option>
                        <option value="damaged" {{ $filter == 'damaged' ? 'selected' : '' }} data-url="{{ route('admin.inventory.filter', ['damaged']) }}">Has damaged stock</option>
                        <option value="needs-review" {{ $filter == 'needs-review' ? 'selected' : '' }} data-url="{{ route('admin.inventory.filter', ['needs-review']) }}">Stock to check</option>
                    @foreach (categories() as $category)
                        <option value="{{$category->slug}}" {{ $filter == $category->slug ? 'selected' : ''}}  data-url="{{route('admin.inventory.filter',[$category->slug])}}">{{$category->name}}</option>
                    @endforeach
                </select>
            </div>
             <form id="form-search" class="flex-auto" action="{{ route('admin.inventory.search')}}" method="get">
                    <div class="search-input">
                        <span class="icon-right"><i class="fas fa-search"></i></span>
                        <input type="text" id="keyword" name="keyword" placeholder="Search by product name, SKU or variant SKU" value="{{ $keyword }}" aria-label="Search inventory">
                        <a href="{{ route('admin.inventory') }}" class="{{ $keyword ? '' : 'hidden' }}" aria-label="Clear search"><span class="icon-right"><i class="fas fa-times"></i></span></a>
                    </div>
            </form>
        </div>
    </div>


    @if (session('success'))
        <div class="alert alert-success m-t-1" role="status">{{ session('success') }}</div>
    @endif

    {{-- One row per variant, and one per product without variants. --}}
    <table class="table inventory-table">
        <thead>
            <tr>
                <th class="column-image"><span class="sr-only">Photo</span></th>
                <th class="column-product">Product</th>
                <th class="column-base">SKU</th>
                <th class="column-base">Category</th>
                <th class="column-base column-num" title="Total on hand: damaged + available">Quantity</th>
                <th class="column-base column-num">Damaged</th>
                <th class="column-base column-num" title="Can be sold">Available</th>
                <th class="column-action"><span class="sr-only">Adjust stock</span></th>
            </tr>
        </thead>
        <tbody>
            @if ($rows->isEmpty())
                <tr class="tr-center">
                    <td colspan="8">{{ $keyword ? 'Nothing matches "' . $keyword . '".' : ($filter === 'damaged' ? 'No damaged stock.' : 'No products found.') }}</td>
                </tr>
            @endif
            @foreach ($rows as $row)
                @php $label = $row->product->name . ($row->variant ? ' ' . $row->variant : ''); @endphp
                <tr class="{{ $row->qty <= 0 ? 'is-out' : '' }}" data-stock-row>
                    <td class="column-image">
                        <div class="image-50"><img src="{{ $row->image }}" alt="" loading="lazy"></div>
                    </td>
                    <td class="column-product">
                        <div class="inventory-item">
                            <a class="item-name link-primary capitalize" href="{{ route('admin.inventory.history', [$row->product->encryptedId()]) }}" title="Stock details">{{ $row->product->name }}</a>
                            @if ($row->variant)<a class="inventory-variant__label inventory-variant__link" href="{{ route('admin.inventory.history', [$row->product->encryptedId(), 'variant' => $row->sku->id]) }}" title="Stock details for {{ $row->variant }}">{{ $row->variant }}</a>@endif
                        </div>
                        @if ($row->firstOfProduct && !$row->product->variant_stock_reviewed)
                            <form method="post" action="{{ route('admin.inventory.reviewed', [$row->product->encryptedId()]) }}" class="inventory-review">
                                @csrf
                                @method('put')
                                <span>Stock was split evenly across sizes.</span>
                                <button class="link-button">Mark as checked</button>
                            </form>
                        @endif
                    </td>
                    <td class="column-base inv-sku">{{ $row->code ?: '—' }}</td>
                    <td class="column-base capitalize">{{ optional($row->product->category)->name }}</td>
                    <td class="column-base column-num"><span class="inv-total" data-total>{{ $row->qty + $row->damaged }}</span></td>
                    <td class="column-base column-num"><span class="inv-damaged {{ $row->damaged > 0 ? 'has-damaged' : '' }}" data-damaged>{{ $row->damaged }}</span></td>
                    <td class="column-base column-num"><span class="stock inv-available" data-available>{{ $row->qty }}</span></td>
                    <td class="column-action">
                        <button type="button" class="btn btn-light btn-adjust" data-no-loading
                                data-url="{{ route('admin.inventory.adjust', [$row->product->encryptedId()]) }}"
                                data-sku="{{ optional($row->sku)->id }}"
                                data-label="{{ $label }}"
                                data-available="{{ $row->qty }}"
                                data-damaged="{{ $row->damaged }}"
                                aria-label="Adjust stock for {{ $label }}">
                            <i class="fas fa-sliders" aria-hidden="true"></i> Adjust
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 mb-2 right mr10">
    {{ $rows->links() }}
</div>

{{-- Adjust stock panel (one for the page; filled from the row's button). --}}
<div class="adj-overlay" data-adjust-panel hidden>
    <form class="adj-panel" role="dialog" aria-modal="true" aria-labelledby="adj-title" data-no-loading novalidate>
        <div class="adj-head">
            <h2 id="adj-title" class="dash-card__title">Adjust stock · <span class="capitalize" data-adj-label></span></h2>
            <button type="button" class="adj-close" data-adj-close aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>

        <fieldset class="adj-group">
            <legend>Stock</legend>
            <div class="adj-options">
                <label><input type="radio" name="action" value="stock_in" checked> <span><strong>Stock In</strong><small>New delivery, found units, count correction</small></span></label>
                <label><input type="radio" name="action" value="stock_out"> <span><strong>Stock Out</strong><small>Lost or missing, count correction</small></span></label>
            </div>
        </fieldset>
        <fieldset class="adj-group">
            <legend>Damage</legend>
            <div class="adj-options">
                <label><input type="radio" name="action" value="damage"> <span><strong>Mark as damaged</strong><small>Moves units out of Available</small></span></label>
                <label><input type="radio" name="action" value="repaired"> <span><strong>Repaired, back to stock</strong><small>Damaged units become sellable again</small></span></label>
                <label><input type="radio" name="action" value="write_off"> <span><strong>Write off</strong><small>Damaged units are thrown away</small></span></label>
            </div>
        </fieldset>

        <div class="adj-fields">
            <div class="form-block adj-qty">
                <label for="adj-qty">Quantity</label>
                <input type="number" id="adj-qty" name="qty" min="1" step="1" value="1" required>
            </div>
            <div class="form-block adj-note">
                <label for="adj-note">Note <span class="label-hint">(optional)</span></label>
                <input type="text" id="adj-note" name="note" maxlength="255" placeholder="Torn seam, supplier box 3">
            </div>
        </div>
        <p class="adj-error" data-adj-error role="alert"></p>

        <div class="adj-preview" aria-live="polite">
            <div><span>Available</span><strong data-adj-available></strong></div>
            <div><span>Damaged</span><strong data-adj-damaged></strong></div>
        </div>

        <div class="adj-actions">
            <button type="button" class="btn btn-light" data-adj-close>Cancel</button>
            <button type="submit" class="btn btn-primary" data-adj-save>Save</button>
        </div>
    </form>
</div>
<div class="adj-toast" data-adj-toast role="status" hidden></div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-inventory.js') }}?v=2"></script>
@endpush
