<footer class="footer site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand">
                <a href="{{ route('home') }}" class="wordmark" aria-label="Coda home">Coda</a>
                <p>Considered essentials in natural fabrics. Cut simply, worn often.</p>
                <ul class="footer-social" role="list">
                    @foreach (socialList() as $site)
                        <li>
                            <a href="{{ $site->url }}" aria-label="{{ $site->name ?? 'Social link' }}" rel="noopener" target="_blank"><i class="{{ $site->code }}" aria-hidden="true"></i></a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="footer-col footer-newsletter" id="newsletter">
                <h2 class="footer-heading">Newsletter</h2>
                <p>New arrivals and sales, a few times a month.</p>
                @if (session('newsletter_success'))
                    <p class="footer-newsletter__ok" role="status"><i class="fas fa-check" aria-hidden="true"></i> {{ session('newsletter_success') }}</p>
                @else
                    <form method="post" action="{{ route('newsletter.subscribe') }}" class="footer-newsletter__form">
                        @csrf
                        <label for="newsletter-email" class="sr-only">Email address</label>
                        <input type="email" id="newsletter-email" name="email" placeholder="Your email" autocomplete="email" required value="{{ old('email') }}" @error('email', 'newsletter') aria-invalid="true" aria-describedby="newsletter-error" @enderror>
                        <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <button class="btn btn-dark">Sign up</button>
                    </form>
                    @error('email', 'newsletter')<p id="newsletter-error" class="footer-newsletter__error" role="alert">{{ $message }}</p>@enderror
                @endif
            </div>

            <nav class="footer-col" aria-label="Shop">
                <h2 class="footer-heading">Shop</h2>
                <ul role="list">
                    <li><a href="{{ route('shop') }}">All products</a></li>
                    <li><a href="{{ route('search') }}">Search</a></li>
                    <li><a href="{{ route('cart') }}">Your bag</a></li>
                </ul>
            </nav>

            <nav class="footer-col" aria-label="Help">
                <h2 class="footer-heading">Help</h2>
                <ul role="list">
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                    <li><a href="{{ route('account') }}">Your account</a></li>
                    <li><a href="{{ route('account.orders', ['all']) }}">Track an order</a></li>
                    @foreach (\App\Models\Page::footer('help') as $footerPage)
                        <li><a href="{{ $footerPage->url() }}">{{ $footerPage->title }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <nav class="footer-col" aria-label="Company">
                <h2 class="footer-heading">Company</h2>
                <ul role="list">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Visit us</a></li>
                    @foreach (\App\Models\Page::footer('company') as $footerPage)
                        <li><a href="{{ $footerPage->url() }}">{{ $footerPage->title }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} Coda. All rights reserved.</span>
            <span class="footer-pay" aria-label="Accepted payments">
                <i class="fab fa-cc-visa" aria-hidden="true"></i>
                <i class="fab fa-cc-mastercard" aria-hidden="true"></i>
                <i class="fab fa-cc-paypal" aria-hidden="true"></i>
            </span>
        </div>
    </div>
</footer>
