<div class="footer">
    <div class="container">
        {{-- <div class="footer-row">
            <div class="column">
                <div class="footer-menu">
                    <div class="footer-categories">
                        <h2>CATEGORIES</h2>
                        <ul>
                        <li><a href="#">Shoes</a></li>
                        <li><a href="#">Jacket</a></li>
                        <li><a href="#">T-shirt</a></li>
                        <li><a href="#">Pants</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="footer-menu">
                    <div class="footer-categories">
                        <h2>HELP</h2>
                        <ul>
                        <li><a href="#">Track Order</a></li>
                        <li><a href="#">Return</a></li>
                        <li><a href="#">Shipping</a></li>
                        <li><a href="#">FAQs</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            

            <div class="column-2">
                <div class="footer-menu">
                    <div class="footer-categories">
                        <h2>GET IN TOUCH</h2>
                        <p>Any Question?</p> 
                        
                        <p>let us known in store at 8th Floor 4356 hudson
                            St. New York or call us (+) 96 716 5869</p>
                    
                        <ul class="social-icon">
                            <li><a href="https://web.facebook.com/"><i class="fab fa-facebook"></i></a></li>
                            <li><a href="https://web.facebook.com/"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="https://web.facebook.com/"><i class="fab fa-twitter"></i></a></li>
                        </ul>    
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="footer-menu">
                    <div class="footer-categories">
                        <h2>NEWSLETTER</h2>
                        <p class="newsletter">codehev.example.com</p>
                        <div class="line"></div>
                        <a class="subcribe" href="$">SUBCRIBE</a>
                    </div>
                </div>
                </div>

                


        </div> --}}
        <div class="footer-block">
            <div class="footer-block-menu">
                <h2 class="footer-block-heading">Quick links</h2>
                <ul class="footer-block-list">
                    <li><a href="/search" class="link link--text list-menu__item list-menu__item--link">SEARCH</a></li>
                    <li><a href="/pages/contact-us" class="link link--text list-menu__item list-menu__item--link">CONTACT US</a></li>
                    <li><a href="/pages/refund-policy" class="link link--text list-menu__item list-menu__item--link">ABOUT</a></li>
                </ul>
            </div>
            <div class="footer-block-menu">
                <ul class="footer-block-list" role="list">
                    @foreach (socialList() as $site)
                        <li class="list-social-item">
                            <a href="{{ $site->url}}"><i class="{{ $site->code }}"></i></a>
                        </li>
                    @endforeach              
                </ul>
            </div> 
        </div>
              
    </div> 
</div>