<section id="collections" class="home-block" aria-labelledby="collections-title">
    <div class="home-block__head">
        <h2 id="collections-title" class="home-block__title">Shop by category</h2>
        <a href="{{ route('shop') }}" class="link-underline">View all</a>
    </div>
    <div class="category-grid">
        @foreach ($collection as $item)
            <a href="{{ route('shop.category', [$item]) }}" class="category-tile">
                <img src="{{ $item->image }}" alt="" loading="lazy">
                <span class="category-tile__label">
                    <span class="capitalize">{{ $item->name }}</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </span>
            </a>
        @endforeach
    </div>
</section>
