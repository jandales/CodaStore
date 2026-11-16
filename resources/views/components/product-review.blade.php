@php
    $userReview = auth()->check() ? auth()->user()->review($product) : null;
    $reviews = $userReview ? $product->reviews->except($userReview->id) : $product->reviews;
@endphp

<div class="review-wrapper">
    @if (session('review_success'))
        <div class="review-note review-note--success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span>{{ session('review_success') }}</span>
        </div>
    @endif

    <div class="review-list">
        <ul>
            @if ($userReview)
                <li>
                    <div class="comment comment--own">
                        <div class="comment-avatar">
                            <img src="{{ auth()->user()->imagePath }}" alt="">
                        </div>
                        <div class="comment-message">
                            <div class="comment-user-rating">
                                <div class="comment-user">
                                    <p>{{ auth()->user()->name }} <span class="review-you">(your review)</span></p>
                                </div>
                                <div class="commnet-rating">
                                    <x-rating :rating="$userReview->rating" />
                                </div>
                            </div>
                            <p class="mt-1">{{ $userReview->comments }}</p>
                            <div class="comment-action mt-1">
                                <ul>
                                    <li>
                                        <button type="button" class="edit-review btn-link" value="{{ $userReview->comments }}" rate="{{ $userReview->rating }}">
                                            <i class="far fa-edit" aria-hidden="true"></i> Edit
                                        </button>
                                    </li>
                                    <li>
                                        <form id="form-review-delete" action="{{ route('review.destroy', [$userReview->encryptedId()]) }}" method="POST" onsubmit="return confirm('Delete your review?');">
                                            @csrf
                                            @method('delete')
                                            <button class="btn-link">
                                                <i class="far fa-trash-alt" aria-hidden="true"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            @foreach ($reviews as $review)
                <li>
                    <div class="comment">
                        <div class="comment-avatar">
                            <img src="{{ $review->user->imagePath }}" alt="">
                        </div>
                        <div class="comment-message">
                            <div class="comment-user-rating">
                                <div class="comment-user">
                                    <p>{{ $review->user->name }}</p>
                                </div>
                                <div class="commnet-rating">
                                    <ul rate-star="{{ $review->rating }}" class="ul-rate-star rate-stars" aria-label="Rated {{ $review->rating }} out of 5"></ul>
                                </div>
                            </div>
                            <p>{{ $review->comments }}</p>
                        </div>
                    </div>
                </li>
            @endforeach

            @if (!$userReview && $reviews->isEmpty())
                <li class="review-empty">No reviews yet. Be the first to share what you think.</li>
            @endif
        </ul>
    </div>

    <div class="add-review-wrapper" id="write-review">
        @auth
            <div class="mt-2">
                <h2>{{ $userReview ? 'Edit your review' : 'Write a review' }}</h2>
                <p class="mt">Your email address won't be published.</p>
            </div>

            <form action="{{ route('review.store', [$product->encryptedId()]) }}" method="POST" class="review-form" novalidate>
                @csrf
                <div class="form-block">
                    <span class="review-label" id="rating-label">Your rating</span>
                    <ul id="rating" class="rate-stars review-stars" role="radiogroup" aria-labelledby="rating-label">
                        @for ($i = 1; $i <= 5; $i++)
                            <li class="rate" rate="{{ $i }}" role="radio" tabindex="0" aria-checked="false" aria-label="{{ $i }} {{ Str::plural('star', $i) }}"><i class="far fa-star" aria-hidden="true"></i></li>
                        @endfor
                    </ul>
                    <input type="hidden" id="input-rate" name="rate" value="{{ old('rate', 0) }}">
                    @error('rate')
                        <small class="error-message" role="alert">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-block">
                    <label for="text-editor">Your review</label>
                    <textarea id="text-editor" name="comments" rows="6" maxlength="1000" placeholder="What did you like? How was the fit?">{{ old('comments') }}</textarea>
                    @error('comments')
                        <small class="error-message" role="alert">{{ $message }}</small>
                    @enderror
                </div>

                <button class="btn btn-dark">{{ $userReview ? 'Update review' : 'Post review' }}</button>
            </form>
        @else
            <div class="review-signin">
                <h2>Write a review</h2>
                <p>Sign in to share your thoughts on this product.</p>
                <a href="{{ route('login') }}" class="btn btn-default">Sign in to write a review</a>
            </div>
        @endauth
    </div>
</div>

<script>
    // Small helpers for the review form; runs after the bundled rating scripts.
    window.addEventListener('load', function () {
        var form = document.querySelector('.review-form');
        var stars = document.querySelectorAll('#rating .rate');
        var input = document.getElementById('input-rate');

        function paint(value) {
            stars.forEach(function (star) {
                var on = parseInt(star.getAttribute('rate'), 10) <= value;
                star.firstElementChild.classList.toggle('fas', on);
                star.firstElementChild.classList.toggle('far', !on);
                star.setAttribute('aria-checked', parseInt(star.getAttribute('rate'), 10) === value ? 'true' : 'false');
            });
        }

        if (input) paint(parseInt(input.value, 10) || 0);

        stars.forEach(function (star) {
            star.addEventListener('click', function () { paint(parseInt(star.getAttribute('rate'), 10)); });
            star.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); star.click(); }
            });
        });

        // Edit: fill the form with the existing review and bring it into view.
        var edit = document.querySelector('.edit-review');
        if (edit && form) {
            edit.addEventListener('click', function () {
                var rate = parseInt(edit.getAttribute('rate'), 10) || 0;
                input.value = rate;
                paint(rate);
                document.getElementById('text-editor').value = edit.getAttribute('value');
                document.getElementById('write-review').scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('text-editor').focus({ preventScroll: true });
            });
        }

        // After posting (or a validation error) open the Reviews tab and scroll to it.
        var hasReviewErrors = @json($errors->has('rate') || $errors->has('comments'));
        var hasReviewNote = @json((bool) session('review_success'));
        if (hasReviewErrors || hasReviewNote || location.hash === '#write-review') {
            var tab = document.querySelector('.tabs-button[data-for-tab="2"]');
            if (tab) tab.click();
            var target = document.getElementById(hasReviewErrors ? 'write-review' : 'product-tabs');
            if (target) target.scrollIntoView({ block: 'start' });
        }
    });
</script>
