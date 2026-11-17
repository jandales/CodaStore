{{-- Save / unsave a product. Usage: @include('components.wishlist-heart', ['product' => $product, 'class' => '...']) --}}
@php $saved = $product->isWishlisted(); @endphp
@auth
    <form action="{{ route('wishlist.toggle', [$product->encryptedId()]) }}" method="POST" class="wish-form {{ $class ?? '' }}">
        @csrf
        <button type="submit" class="wish-btn {{ $saved ? 'is-saved' : '' }}" aria-pressed="{{ $saved ? 'true' : 'false' }}"
                aria-label="{{ $saved ? 'Remove ' . $product->name . ' from wishlist' : 'Save ' . $product->name . ' to wishlist' }}">
            <i class="{{ $saved ? 'fas' : 'far' }} fa-heart" aria-hidden="true"></i>
            @if (!empty($label))<span>{{ $saved ? 'Saved' : 'Save to wishlist' }}</span>@endif
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="wish-btn wish-form {{ $class ?? '' }}" aria-label="Sign in to save {{ $product->name }}">
        <i class="far fa-heart" aria-hidden="true"></i>
        @if (!empty($label))<span>Save to wishlist</span>@endif
    </a>
@endauth
