<nav class="navbar navbar-expand-lg py-0 sticky-top" id="mainNav">
    <div class="container-xl px-4">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-5 py-3" href="/">
            <div class="nav-logo-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-airplane-fill"></i>
            </div>
            <span class="nav-brand-text">AirTicket</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
            <i class="bi bi-list fs-3 text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link nav-pill {{ request()->is('/') ? 'active' : '' }}" href="/"><i class="bi bi-airplane me-1"></i>Flights</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-pill" href="#"><i class="bi bi-building me-1"></i>Hotels</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-pill" href="#"><i class="bi bi-map me-1"></i>Tours</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-pill" href="#"><i class="bi bi-passport me-1"></i>Visa</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3 py-2 py-lg-0">
                <a href="#" class="nav-link nav-pill text-white">Sign In</a>
                <a href="#" class="btn btn-nav-cta">Get Started</a>
            </div>
        </div>
    </div>
</nav>

<style>
    /* ── Default nav: solid dark (works on all pages) ── */
    #mainNav {
        background: rgba(10, 15, 35, 0.96);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255,255,255,0.08);
        box-shadow: 0 4px 30px rgba(0,0,0,0.25);
        transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
    }

    /* ── On home page: start transparent, turn solid on scroll ── */
    body.page-home #mainNav {
        background: linear-gradient(180deg, rgba(0,0,0,0.70) 0%, rgba(0,0,0,0) 100%);
        backdrop-filter: blur(0px);
        border-bottom-color: transparent;
        box-shadow: none;
    }
    body.page-home #mainNav.nav-scrolled {
        background: rgba(10, 15, 35, 0.96) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom-color: rgba(255,255,255,0.08) !important;
        box-shadow: 0 4px 30px rgba(0,0,0,0.3) !important;
    }

    .nav-logo-circle {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        border-radius: 10px;
        color: white;
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(59,130,246,0.4);
        flex-shrink: 0;
    }
    .nav-brand-text {
        color: white;
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .nav-pill {
        color: rgba(255,255,255,0.85) !important;
        padding: 8px 16px !important;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .nav-pill:hover, .nav-pill.active {
        color: white !important;
        background: rgba(255,255,255,0.12) !important;
    }
    .btn-nav-cta {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: white !important;
        border: none;
        padding: 9px 22px;
        border-radius: 22px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(59,130,246,0.35);
    }
    .btn-nav-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(59,130,246,0.5);
        color: white !important;
    }
    .navbar-toggler:focus { box-shadow: none; }
</style>

<script>
    // Scroll-to-opaque only applies on the home page (body.page-home)
    if (document.body.classList.contains('page-home')) {
        window.addEventListener('scroll', function () {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 20) {
                nav.classList.add('nav-scrolled');
            } else {
                nav.classList.remove('nav-scrolled');
            }
        }, { passive: true });
    }
</script>