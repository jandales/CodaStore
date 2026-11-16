@php
    $sorts = [
        'all' => 'Recommended',
        'featured-product' => 'Featured',
        'new-to-old' => 'Newest',
        'low-to-high' => 'Price: low to high',
        'high-to-low' => 'Price: high to low',
        'a-z' => 'Name: A to Z',
        'z-a' => 'Name: Z to A',
        'old-to-new' => 'Oldest',
    ];
    $currentSort = request()->route('value') ?? 'all';
@endphp
<div class="shop-toolbar">
    <nav class="shop-filters" aria-label="Categories">
        <ul role="list">
            <li><a href="{{ route('shop') }}" class="chip" @if(request()->routeIs('shop', 'shop.sort')) aria-current="page" @endif>All</a></li>
            @foreach (categories() as $category)
                <li><a href="{{ route('shop.category', [$category]) }}" class="chip capitalize" @if(request()->is('shop/' . $category->slug)) aria-current="page" @endif>{{ $category->name }}</a></li>
            @endforeach
        </ul>
    </nav>
    <div class="shop-sort">
        <label for="sorting">Sort by</label>
        <select name="sortBy" id="sorting">
            @foreach ($sorts as $key => $label)
                <option value="{{ route('shop.sort', [$key]) }}" {{ $currentSort === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="product-grid product-grid--shop">
    @if (count($products) == 0)
        <div class="empty-state">
            <h2>No products found</h2>
            <p>Try another category or clear your sort.</p>
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