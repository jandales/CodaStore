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
                </ul>
            </nav>

            <nav class="footer-col" aria-label="Company">
                <h2 class="footer-heading">Company</h2>
                <ul role="list">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Visit us</a></li>
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
