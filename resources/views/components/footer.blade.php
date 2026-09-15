<footer class="site-footer">
    <div class="container">
        <div class="row g-5 footer-top">
            <!-- Brand column -->
            <div class="col-lg-4 col-md-6">
                <a class="footer-brand" href="/">
                    <div class="footer-logo-icon">
                        <i class="bi bi-airplane-fill"></i>
                    </div>
                    <span>AirTicket</span>
                </a>
                <p class="footer-tagline">The world's smartest flight search engine. Compare hundreds of airlines and find the cheapest fares in seconds.</p>
                <div class="footer-socials">
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Explore -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading">Explore</h6>
                <ul class="footer-links">
                    <li><a href="#">Flights</a></li>
                    <li><a href="#">Hotels</a></li>
                    <li><a href="#">Tours</a></li>
                    <li><a href="#">Visa Services</a></li>
                    <li><a href="#">Travel Insurance</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading">Company</h6>
                <ul class="footer-links">
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-heading">Stay Updated</h6>
                <p style="color: rgba(255,255,255,0.45); font-size: 0.88rem; margin-bottom: 16px;">Get exclusive deals and travel tips delivered to your inbox.</p>
                <div class="footer-subscribe">
                    <input type="email" placeholder="your@email.com">
                    <button type="button">Subscribe</button>
                </div>
                <div class="footer-badges mt-4">
                    <div class="f-badge"><i class="bi bi-shield-check text-success me-2"></i>Secure Payments</div>
                    <div class="f-badge"><i class="bi bi-lock text-primary me-2"></i>SSL Encrypted</div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} AirTicket. All rights reserved.</p>
            <div class="footer-legal-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<style>
    .site-footer {
        background: #080e1f;
        padding: 80px 0 0;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .footer-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: white;
        font-weight: 800;
        font-size: 1.3rem;
        margin-bottom: 18px;
        letter-spacing: -0.5px;
    }

    .footer-logo-icon {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: white;
        font-size: 0.95rem;
    }

    .footer-tagline {
        color: rgba(255,255,255,0.42);
        font-size: 0.88rem;
        line-height: 1.75;
        margin-bottom: 24px;
        max-width: 300px;
    }

    .footer-socials {
        display: flex;
        gap: 10px;
    }

    .social-btn {
        width: 38px; height: 38px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: rgba(255,255,255,0.5);
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .social-btn:hover {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        transform: translateY(-2px);
    }

    .footer-heading {
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
    }

    .footer-links {
        list-style: none;
        padding: 0; margin: 0;
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    .footer-links a {
        color: rgba(255,255,255,0.45);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: color 0.2s;
    }

    .footer-links a:hover { color: white; }

    .footer-subscribe {
        display: flex;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        overflow: hidden;
    }

    .footer-subscribe input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 12px 16px;
        color: white;
        font-size: 0.85rem;
        outline: none;
    }

    .footer-subscribe input::placeholder { color: rgba(255,255,255,0.3); }

    .footer-subscribe button {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        border: none;
        color: white;
        padding: 10px 20px;
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        transition: opacity 0.2s;
        border-radius: 0 10px 10px 0;
    }

    .footer-subscribe button:hover { opacity: 0.9; }

    .f-badge {
        display: inline-flex;
        align-items: center;
        color: rgba(255,255,255,0.4);
        font-size: 0.78rem;
        font-weight: 500;
        margin-right: 16px;
    }

    .footer-bottom {
        margin-top: 56px;
        padding: 22px 0;
        border-top: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .footer-bottom p {
        margin: 0;
        color: rgba(255,255,255,0.3);
        font-size: 0.82rem;
    }

    .footer-legal-links {
        display: flex;
        gap: 24px;
    }

    .footer-legal-links a {
        color: rgba(255,255,255,0.3);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 500;
        transition: color 0.2s;
    }

    .footer-legal-links a:hover { color: rgba(255,255,255,0.7); }
</style>
