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
                        <input type="text" id="keyword" name="keyword" placeholder="Search by Name or Sku">
                        <a href="{{ route('admin.inventory') }}" class="{{ $keyword ?? 'hidden'}}"><span class="icon-right"><i class="fas fa-times"></i></span></a>
                    </div>
            </form>
        </div>
    </div>


    <table class="table">
        <thead>
            <tr>
                <th class="column-0"></th>
                <th class="column-image"></th>
                <th class="column-product">Product</th>
                <th class="column-base">Category</th>
                <th class="column-base">Stock</th>
                <th class="column-base">Action</th>
                <th class="column-base">Update Quantity</th>
                <th class="column-action"></th>
            </tr>
        </thead>
        <tbody>
            @if (session('success'))
                <tr><td colspan="9"><div class="alert alert-success" role="status">{{ session('success') }}</div></td></tr>
            @endif
            @if($products->count() == 0)
                <tr class="tr-center">
                    <td colspan="9">No Product Found</td>
                </tr>
            @endif
            @foreach ($products as $product)
                @if ($product->skus->isNotEmpty())
                    {{-- Products with variants: the total, then one adjustable row per variant. --}}
                    <tr class="inventory-product">
                        <td class="column-0"></td>
                        <td class="column-image"><div class="image-50"><img src="{{ $product->imagePath }}" alt="{{ $product->name }}" loading="lazy"></div></td>
                        <td class="column-product">
                            <div class="item-name">{{ $product->name }}</div>
                            @unless ($product->variant_stock_reviewed)
                                <form method="post" action="{{ route('admin.inventory.reviewed', [$product->encryptedId()]) }}" class="inventory-review">
                                    @csrf
                                    @method('put')
                                    <span>Stock was split evenly across sizes.</span>
                                    <button class="link-button">Mark as checked</button>
                                </form>
                            @endunless
                        </td>
                        <td class="column-base">{{ optional($product->category)->name }}</td>
                        <td class="column-base"><strong>{{ $product->stockQty() }}</strong> <span class="label-hint">total</span></td>
                        <td class="column-base" colspan="3"><span class="label-hint">Adjust each variant below</span></td>
                    </tr>
                    @foreach ($product->skus as $sku)
                        <tr class="inventory-variant">
                            <td class="column-0"></td>
                            <td class="column-image">@if ($sku->image)<div class="image-50"><img src="{{ $sku->image }}" alt="" loading="lazy"></div>@endif</td>
                            <td class="column-product"><span class="inventory-variant__label">{{ $sku->label() }}</span>@if ($sku->sku) <span class="label-hint">{{ $sku->sku }}</span>@endif</td>
                            <td class="column-base"></td>
                            <td class="column-base"><span class="stock link-primary">{{ $sku->qty }}</span></td>
                            <td class="column-base">
                                <select name="type" class="type" aria-label="Stock in or out for {{ $product->name }} {{ $sku->label() }}">
                                    <option value="1">Stock In</option>
                                    <option value="0">Stock Out</option>
                                </select>
                            </td>
                            <td class="column-base">
                                <div class="form-group-qty">
                                    <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus"> <i class="fas fa-minus"></i></div>
                                    <input class="num-product bg-grey" item="" type="number" value="0" aria-label="Quantity for {{ $sku->label() }}">
                                    <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add"> <i class="fas fa-plus"></i></div>
                                </div>
                            </td>
                            <td class="column-action">
                                <button disabled data-url="{{ route('admin.inventory.update.variant', [$sku->id]) }}" class="btn btn-primary btn-save">Save</button>
                            </td>
                        </tr>
                    @endforeach
                    @continue
                @endif
                <tr>
                    <td class="column-0"></td>
                    <td class="column-image">
                        <div class="image-50">
                            <img src="{{ $product->imagePath }}" alt="{{ $product->name }}" loading="lazy">
                        </div>
                    </td>
                        <td class="column-product">
                            <div class="product-image-container">

                                <div class="image-description ml-0">
                                    <div class="item-name">{{ $product->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="column-base">{{ $product->category->name }}</td>
                        <td class="column-base"><span class="stock link-primary">{{ $product->stock->qty }}</span></td>
                        <td class="column-base">
                            <select name="type" class="type">
                                <option value="1">Stock In</option>
                                <option value="0">Stock Out</option>
                            </select>
                        </td>
                        <td class="column-base">
                            <div class="form-group-qty">
                                <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus"> <i class="fas fa-minus"></i></div>
                                <input class="num-product bg-grey"  item=""  type="number" value="0">
                                <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add"> <i class="fas fa-plus"></i></div>
                            </div>
                        </td>
                        <td class="column-action">
                            <button disabled  data-url = "{{ route('admin.inventory.update.quantity',[$product->stock->id])}}"class="btn btn-primary btn-save">Save</button>
                        </td>
                    </tr>
            @endforeach

        </tbody>
    </table>
</div>
<div class="mt-2 mb-2 right mr10">
    {{ $products->links() }}
</div>



@endsection