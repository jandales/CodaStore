@php
    $sorts = \App\Services\ShopServices::SORTS;
    $currentSort = request('sort', 'newest');
    $sizes = $filterable ? \App\Services\ShopServices::availableSizes() : [];
    $chosenSizes = (array) request('size', []);
    $active = array_filter([
        'size' => $chosenSizes,
        'price_min' => request('price_min'),
        'price_max' => request('price_max'),
        'sale' => request('sale'),
        'in_stock' => request('in_stock'),
    ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    // Category links keep the current filters.
    $keep = request()->except(['page']);
@endphp
<div class="shop-toolbar">
    <nav class="shop-filters" aria-label="Categories">
        <ul role="list">
            <li><a href="{{ route('shop', $keep) }}" class="chip" @if(request()->routeIs('shop')) aria-current="page" @endif>All</a></li>
            @foreach (categories() as $category)
                <li><a href="{{ route('shop.category', array_merge([$category], $keep)) }}" class="chip capitalize" @if(request()->is('shop/' . $category->slug)) aria-current="page" @endif>{{ $category->name }}</a></li>
            @endforeach
        </ul>
    </nav>
    @if ($filterable)
    <div class="shop-sort">
        <label for="sorting">Sort by</label>
        <select name="sortBy" id="sorting">
            @foreach ($sorts as $key => $label)
                <option value="{{ request()->fullUrlWithQuery(['sort' => $key === 'newest' ? null : $key, 'page' => null]) }}" {{ $currentSort === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @endif
</div>

@if ($filterable)
    <details class="shop-filter-panel" {{ $active ? 'open' : '' }}>
        <summary>
            <i class="fas fa-sliders" aria-hidden="true"></i> Filters
            @if ($active)<span class="shop-filter-count">{{ count($active) }}</span>@endif
        </summary>
        <form method="GET" action="{{ url()->current() }}" class="shop-filter-form">
            @if (request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

            @if ($sizes)
                <fieldset>
                    <legend>Size</legend>
                    <div class="shop-filter-sizes">
                        @foreach ($sizes as $size)
                            <label class="size-check">
                                <input type="checkbox" name="size[]" value="{{ $size }}" {{ in_array($size, $chosenSizes) ? 'checked' : '' }}>
                                <span>{{ $size }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <fieldset>
                <legend>Price (₱)</legend>
                <div class="shop-filter-price">
                    <label class="sr-only" for="price_min">Minimum price</label>
                    <input type="number" id="price_min" name="price_min" min="0" step="1" placeholder="Min" value="{{ request('price_min') }}">
                    <span aria-hidden="true">to</span>
                    <label class="sr-only" for="price_max">Maximum price</label>
                    <input type="number" id="price_max" name="price_max" min="0" step="1" placeholder="Max" value="{{ request('price_max') }}">
                </div>
            </fieldset>

            <fieldset>
                <legend>Show</legend>
                <label class="check-line"><input type="checkbox" name="sale" value="1" {{ request('sale') ? 'checked' : '' }}> On sale only</label>
                <label class="check-line"><input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}> In stock only</label>
            </fieldset>

            <div class="shop-filter-actions">
                <button type="submit" class="btn btn-dark">Apply filters</button>
                @if ($active)
                    <a href="{{ url()->current() . (request('sort') ? '?sort=' . urlencode(request('sort')) : '') }}" class="link-underline">Clear all</a>
                @endif
            </div>
        </form>
    </details>

    @if ($active)
        <ul class="shop-active-filters" role="list" aria-label="Active filters">
            @foreach ($chosenSizes as $size)
                <li><a href="{{ request()->fullUrlWithQuery(['size' => array_values(array_diff($chosenSizes, [$size])), 'page' => null]) }}" class="chip">Size {{ $size }} <i class="fas fa-xmark" aria-hidden="true"></i><span class="sr-only">Remove</span></a></li>
            @endforeach
            @if (request('price_min') || request('price_max'))
                <li><a href="{{ request()->fullUrlWithQuery(['price_min' => null, 'price_max' => null, 'page' => null]) }}" class="chip">₱{{ request('price_min') ?: 0 }} to {{ request('price_max') ? '₱' . request('price_max') : 'any' }} <i class="fas fa-xmark" aria-hidden="true"></i><span class="sr-only">Remove</span></a></li>
            @endif
            @if (request('sale'))
                <li><a href="{{ request()->fullUrlWithQuery(['sale' => null, 'page' => null]) }}" class="chip">On sale <i class="fas fa-xmark" aria-hidden="true"></i><span class="sr-only">Remove</span></a></li>
            @endif
            @if (request('in_stock'))
                <li><a href="{{ request()->fullUrlWithQuery(['in_stock' => null, 'page' => null]) }}" class="chip">In stock <i class="fas fa-xmark" aria-hidden="true"></i><span class="sr-only">Remove</span></a></li>
            @endif
        </ul>
    @endif
@endif

<div class="product-grid product-grid--shop">
    @if (count($products) == 0)
        <div class="empty-state">
            <h2>No products found</h2>
            <p>Try removing a filter or choosing another category.</p>
            <a href="{{ route('shop') }}" class="btn btn-default">View all products</a>
        </div>
    @else
        @foreach ($products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach
    @endif
</div>
    @if (count($products) != 0)
        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
    @endif