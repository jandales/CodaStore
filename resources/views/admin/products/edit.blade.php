@extends('layout.admin')
@section('content')

<div class="container">
    <div class="page-title">
        <h1>Edit Products</h1>
    </div>
    <div id="notify-message" class="m-t-2">

    </div>
    <form id="form" method="post"  action="{{route('admin.products.update',[ $product->encryptedId() ])}}" enctype="multipart/form-data">
        @csrf

        <div class="page-container m-t-2">
            <div class="left-column">
                <div class="panel">
                <div class="panel-body">
                        <div class="form-block">
                            <label for="name">Name</label>
                            <input type="text" name="name" value="{{$product->name}}">
                        </div>
                        <div class="form-block">
                            <label for="email">Short Description</label>
                        <textarea  name="short_description"   cols="30" rows="3">{{$product->short_description}}</textarea>
                        </div>
                        <div class="form-block">
                            <label for="email">Long Description</label>
                            <textarea  name="long_description"   cols="30" rows="10">{{$product->long_description}}</textarea>
                        </div>
                </div>
                </div>
                <!-- Media: main image and gallery, drag and drop --->
                @include('admin.products._media')
                <!-- /media --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Pricing</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-inline gap30">
                            <div class="form-block col-6">
                                <label for="regular_price">Regular price <span class="label-hint">(optional)</span></label>
                                <input type="number" id="regular_price" name="regular_price" min="0" step="0.01" value="{{ $product->regular_price }}">
                            </div>
                            <div class="form-block col-6">
                                <label for="sale_price">Sale price <span class="label-hint">(optional)</span></label>
                                <input type="number" id="sale_price" name="sale_price" min="0" step="0.01" value="{{ $product->sale_price ?: '' }}">
                            </div>
                        </div>
                        <label class="sale-toggle"><input type="checkbox" name="on_sale" value="1" {{ $product->on_sale ? 'checked' : '' }}> On sale: customers pay the sale price</label>
                        @if ($product->isOnSale())
                            <p class="label-hint">Currently {{ $product->discountPercent() }}% off.</p>
                        @endif
                    </div>
                </div>
                <!-- invetory --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Inventory</label>
                    </div>
                    <div class="panel-body">
                        <div class="form-inline gap30">
                            <div class="form-block col-6">
                                <label for="name">SKU <span class="label-hint">(optional)</span></label>
                                <input type="text"  name="sku" value="{{ $product->sku}}">
                            </div>
                            <div class="form-block col-6">
                                <label for="name">Barcode <span class="label-hint">(optional)</span></label>
                                <input type="text" name="barcode" value="{{ $product->barcode}}">
                            </div>
                        </div>
                        <div class="form-block">
                            <label for="name">Quantity</label>
                            <input type="number" name="qty" value="{{$product->stock->qty}}">
                            @if ($product->damagedQty() > 0)
                                <p class="form-help">Available stock. {{ $product->damagedQty() }} damaged {{ $product->damagedQty() === 1 ? 'unit is' : 'units are' }} kept apart: <a href="{{ route('admin.inventory.history', [$product->encryptedId()]) }}">manage on Inventory</a>.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- /inventory --->
                <!-- Variants --->
                @include('admin.products._variants')
                <!-- /variants --->
            </div>
            <div class="right-column">
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title"></label>
                    </div>
                    <div class="panel-body">
                        <div class="form-block">
                            <select id="status" name="status" >
                                <option {{$product->status == 0 ? 'selected' : '' }} value="0">Draft</option>
                                <option {{$product->status == 1 ? 'selected' : '' }}  value="1">Published</option>
                            </select>
                        </div>
                        <div class="form-inline">
                            <input type="checkbox" {{ $product->featured == 1 ? 'checked' : ''}} name="featured" value="1">
                            <label for="" class="ml10">Featured Product</label>
                        </div>
                        <div class="form-block w-12">
                            <button id="btn-update" class="btn btn-primary align-self-end">Save</button>
                        </div>
                    </div>
                </div>
                <!-- categories --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Category</label>
                    </div>
                    <div class="panel-body">
                    <div class="form-inline">
                            <div class="form-block w-12">
                                <select id="categories" class="capitalized" name="category_id">
                                    <option value="0">Uncategories</option>
                                    @foreach (categories() as $category)
                                        <option {{ $product->category_id == $category->id ? 'selected' : ''}} value="{{ $category->id }}" >{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    </div>
                </div>
                <!-- /categories --->
                <!-- Tags --->
                <div class="panel">
                    <div class="panel-heading">
                        <label for="" class="panel-title">Tags</label>
                    </div>
                    <div class="panel-body">
                            <div class="form-block">
                                <input type="text" name="tags" value="{{ $product->tags }}">
                            </div>
                            <span>Separate tags with commas</span>
                    </div>
                </div>
                <!-- /Tags --->


            </div>
        </div>
    </form>
    <form id="formUpload" method="post"  enctype="multipart/form-data">
        @csrf
    </form>
</div>



@push('scripts')
<script src="{{ asset('js/admin-media.js') }}?v=2"></script>
<script src="{{ asset('js/admin-variants.js') }}?v=3"></script>
@endpush
@endsection
