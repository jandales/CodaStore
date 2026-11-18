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
                <th class="column-base">Available</th>
                <th class="column-base">Action</th>
                <th class="column-base">Update quantity</th>
                <th class="column-action"><span class="sr-only">Save</span></th>
            </tr>
        </thead>
        <tbody>
            @if ($rows->isEmpty())
                <tr class="tr-center">
                    <td colspan="8">{{ $keyword ? 'Nothing matches "' . $keyword . '".' : 'No products found.' }}</td>
                </tr>
            @endif
            @foreach ($rows as $row)
                @php $label = $row->product->name . ($row->variant ? ' ' . $row->variant : ''); @endphp
                <tr class="{{ $row->qty <= 0 ? 'is-out' : '' }}">
                    <td class="column-image">
                        <div class="image-50"><img src="{{ $row->image }}" alt="" loading="lazy"></div>
                    </td>
                    <td class="column-product">
                        <div class="inventory-item">
                            <a class="item-name link-primary capitalize" href="{{ route('admin.products.edit', [$row->product->encryptedId()]) }}">{{ $row->product->name }}</a>
                            @if ($row->variant)<span class="inventory-variant__label">{{ $row->variant }}</span>@endif
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
                    <td class="column-base">{{ $row->code ?: '—' }}</td>
                    <td class="column-base capitalize">{{ optional($row->product->category)->name }}</td>
                    <td class="column-base"><span class="stock link-primary">{{ $row->qty }}</span></td>
                    <td class="column-base">
                        <select name="type" class="type" aria-label="Stock in or out for {{ $label }}">
                            <option value="1">Stock In</option>
                            <option value="0">Stock Out</option>
                        </select>
                    </td>
                    <td class="column-base">
                        <div class="form-group-qty">
                            <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus" role="button" aria-label="Less"><i class="fas fa-minus" aria-hidden="true"></i></div>
                            <input class="num-product bg-grey" item="" type="number" value="0" aria-label="Quantity to change for {{ $label }}">
                            <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add" role="button" aria-label="More"><i class="fas fa-plus" aria-hidden="true"></i></div>
                        </div>
                    </td>
                    <td class="column-action">
                        @if ($row->saveUrl)
                            <button disabled data-url="{{ $row->saveUrl }}" class="btn btn-primary btn-save">Save</button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 mb-2 right mr10">
    {{ $rows->links() }}
</div>
@endsection
