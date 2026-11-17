@extends('layout.front.app')

@section('title', 'Wishlist | Coda')

@section('content')
    <div class="container">
        <div class="flex account mt-3 mb-3">
            <div class="col1">
                @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card no-border pad-2 bg-grey">
                    <div class="card-heading">
                        <h2>Wishlist</h2>
                        @if ($items->isNotEmpty())
                            <span class="pdp-muted">{{ $items->count() }} {{ Str::plural('item', $items->count()) }}</span>
                        @endif
                    </div>

                    @if (session('wishlist_message'))
                        <div class="review-note review-note--success" role="status">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span>{{ session('wishlist_message') }}</span>
                        </div>
                    @endif

                    @if ($items->isEmpty())
                        <div class="empty-state account-empty">
                            <h2>Your wishlist is empty</h2>
                            <p>Tap the heart on any product to save it for later.</p>
                            <a href="{{ route('shop') }}" class="btn btn-default">Browse the shop</a>
                        </div>
                    @else
                        <div class="product-grid wishlist-grid">
                            @foreach ($items as $item)
                                <div class="wishlist-item">
                                    @include('components.product-card', ['product' => $item->product])
                                    <form action="{{ route('wishlist.remove', [$item->product->encryptedId()]) }}" method="POST" class="wishlist-item__remove">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-link">Remove</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
