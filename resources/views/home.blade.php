@extends('layouts.master')
@section('bodyClass', 'page-home')

@section('content')
<style>
    /* ===== CSS VARIABLES ===== */
    :root {
        --blue: #3b82f6;
        --blue-dark: #1d4ed8;
        --indigo: #6366f1;
        --amber: #f59e0b;
        --surface: #ffffff;
        --surface-2: #f8fafc;
        --border: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #64748b;
        --card-radius: 20px;
        --input-radius: 14px;
        --shadow-soft: 0 4px 24px rgba(0,0,0,0.06);
        --shadow-med: 0 8px 40px rgba(0,0,0,0.12);
        --shadow-strong: 0 20px 60px rgba(0,0,0,0.2);
    }

    .hero-section {
        position: relative;
        z-index: 10;
        /* Pull hero up behind the sticky navbar (navbar ≈ 72px tall) */
        margin-top: -72px;
        min-height: 100vh;
        background-image: url('{{ asset('hero-bg.png') }}');
        background-size: cover;
        background-position: center 40%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding-bottom: 180px;
        overflow: visible; /* Ensure it doesn't clip the dropdown */
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(0,0,0,0.15) 0%,
            rgba(5,10,30,0.55) 60%,
            rgba(5,10,30,0.85) 100%
        );
        z-index: 1;
    }

    /* Animated background particles */
    .hero-particles {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
    }
    .particle {
        position: absolute;
        width: 2px;
        height: 2px;
        background: rgba(255,255,255,0.6);
        border-radius: 50%;
        animation: float-particle linear infinite;
    }

    @keyframes float-particle {
        0% { transform: translateY(100vh) scale(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 0.8; }
        100% { transform: translateY(-20px) scale(1.5); opacity: 0; }
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        color: white;
        max-width: 780px;
        padding: 0px 20px;
        margin-bottom: 48px;
        margin-top: 80px;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(59,130,246,0.2);
        border: 1px solid rgba(59,130,246,0.4);
        color: #93c5fd;
        padding: 6px 18px;
        border-radius: 40px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 24px;
        backdrop-filter: blur(10px);
        animation: fade-in-down 0.6s ease both;
    }

    .hero-badge .dot {
        width: 6px;
        height: 6px;
        background: #3b82f6;
        border-radius: 50%;
        animation: pulse-dot 1.5s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.6); opacity: 0.5; }
    }

    .hero-title {
        font-size: clamp(2.8rem, 6vw, 5rem);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -2px;
        margin-bottom: 22px;
        text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        animation: fade-in-up 0.7s 0.1s ease both;
    }

    .hero-title .highlight {
        background: linear-gradient(120deg, #60a5fa, #a78bfa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.15rem;
        font-weight: 400;
        opacity: 0.85;
        margin-bottom: 0;
        animation: fade-in-up 0.7s 0.2s ease both;
    }

    .hero-stats {
        display: flex;
        gap: 40px;
        justify-content: center;
        margin-top: 32px;
        animation: fade-in-up 0.7s 0.3s ease both;
    }

    .hero-stat {
        text-align: center;
    }

    .hero-stat strong {
        display: block;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: white;
    }

    .hero-stat span {
        font-size: 0.78rem;
        color: rgba(255,255,255,0.65);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-divider {
        width: 1px;
        background: rgba(255,255,255,0.2);
        align-self: stretch;
    }

    /* ===== SEARCH PANEL ===== */
    .search-panel-wrapper {
        position: relative;
        z-index: 100;
        max-width: 1200px;
        width: 100%;
        padding: 0 20px;
    }

    .search-panel-inner {
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
        border-radius: 24px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.5);
        padding: 28px 28px 24px;
        animation: panel-rise 0.7s 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes panel-rise {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Trip type pills */
    .trip-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 18px;
    }

    .trip-tab {
        padding: 8px 22px;
        border-radius: 30px;
        border: 1.5px solid var(--border);
        background: transparent;
        color: var(--text-secondary);
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
    }

    .trip-tab:hover {
        border-color: var(--blue);
        color: var(--blue);
    }

    .trip-tab.active {
        background: var(--blue);
        border-color: var(--blue);
        color: white;
        box-shadow: 0 4px 15px rgba(59,130,246,0.35);
    }

    /* Search inputs row */
    .search-inputs {
        display: flex;
        align-items: stretch;
        background: #f1f5f9;
        border-radius: var(--input-radius);
        overflow: visible;
        gap: 1px;
    }

    .s-field {
        flex: 1;
        min-width: 0;
        background: white;
        padding: 16px 22px;
        position: relative;
        transition: background 0.2s;
        cursor: pointer;
    }

    .s-field:first-child {
        border-top-left-radius: var(--input-radius);
        border-bottom-left-radius: var(--input-radius);
    }

    .s-field:focus-within {
        background: #eff6ff;
        z-index: 5;
    }

    .s-field label {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 4px;
    }

    .s-field input, .s-field .s-display {
        border: none;
        background: transparent;
        width: 100%;
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-primary);
        padding: 0;
        outline: none;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .s-field input::placeholder {
        font-weight: 500;
        color: #cbd5e1;
    }

    .s-field .s-sub {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-top: 1px;
        font-weight: 400;
    }

    .s-separator {
        width: 1px;
        background: #e2e8f0;
        align-self: stretch;
    }

    /* Swap button */
    .swap-btn {
        position: absolute;
        right: -18px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        width: 36px;
        height: 36px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--blue);
        cursor: pointer;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-size: 0.8rem;
    }

    .swap-btn:hover {
        background: var(--blue);
        border-color: var(--blue);
        color: white;
        transform: translateY(-50%) rotate(180deg) scale(1.1);
    }

    /* Date field with calendar icon */
    .s-field .cal-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
        pointer-events: none;
    }

    .s-field input[type="date"] {
        -webkit-appearance: none;
        appearance: none;
    }
    .s-field input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        cursor: pointer;
        position: absolute;
        right: 14px;
        width: 24px;
        height: 24px;
    }

    /* Traveler field */
    .traveler-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 340px;
        background: white;
        border-radius: 20px;
        box-shadow: var(--shadow-strong);
        padding: 24px;
        z-index: 1000;
        display: none;
        border: 1px solid var(--border);
        animation: dropIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .traveler-dropdown.show { display: block; }

    @keyframes dropIn {
        from { opacity: 0; transform: translateY(10px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .t-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .t-row:last-of-type { border-bottom: none; }

    .t-info h6 { margin: 0; font-weight: 700; font-size: 0.92rem; color: var(--text-primary); }
    .t-info p { margin: 0; font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

    .t-controls {
        display: flex;
        align-items: center;
        gap: 16px;
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-primary);
        min-width: 90px;
        justify-content: center;
    }

    .t-btn {
        width: 32px; height: 32px;
        border-radius: 50%;
        border: 1.5px solid #e2e8f0;
        background: white;
        display: flex; align-items: center; justify-content: center;
        color: #64748b;
        transition: all 0.2s;
        cursor: pointer;
        font-size: 0.85rem;
    }
    .t-btn:hover {
        border-color: var(--blue);
        color: var(--blue);
        background: #eff6ff;
    }
    .t-btn:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .cabin-section { border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: 4px; }
    .cabin-section h6 { font-weight: 700; font-size: 0.85rem; margin-bottom: 12px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }

    .cabin-options { display: flex; gap: 8px; flex-wrap: wrap; }
    .cabin-chip {
        padding: 6px 16px;
        border-radius: 20px;
        border: 1.5px solid #e2e8f0;
        background: transparent;
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cabin-chip:hover { border-color: var(--blue); color: var(--blue); }
    .cabin-chip.active {
        background: var(--blue);
        border-color: var(--blue);
        color: white;
    }

    .btn-done-new {
        width: 100%;
        background: linear-gradient(135deg, var(--blue), var(--indigo));
        color: white;
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        margin-top: 18px;
        transition: all 0.3s;
        cursor: pointer;
    }
    .btn-done-new:hover { opacity: 0.92; transform: translateY(-1px); }

    /* Search Button */
    .search-cta-field {
        flex: 0 0 auto;
    }

    .btn-search-main {
        height: 100%;
        min-height: 80px;
        background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        color: white;
        border: none;
        border-top-right-radius: var(--input-radius);
        border-bottom-right-radius: var(--input-radius);
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        padding: 0 36px;
        font-weight: 800;
        font-size: 1rem;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        box-shadow: 4px 0 0 rgba(99,102,241,0.3) inset;
        cursor: pointer;
    }

    .btn-search-main:hover {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        padding-right: 44px;
        box-shadow: -4px 0 30px rgba(99,102,241,0.4);
    }

    .btn-search-main i {
        font-size: 1.1rem;
        transition: transform 0.3s;
    }

    .btn-search-main:hover i {
        transform: translateX(4px);
    }

    /* Popular destinations section */
    .section-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: var(--blue);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 12px;
    }

    .section-title {
        font-size: clamp(1.8rem, 3.5vw, 2.6rem);
        font-weight: 900;
        letter-spacing: -1.5px;
        line-height: 1.1;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .section-subtitle {
        color: var(--text-secondary);
        font-size: 1rem;
        font-weight: 400;
    }

    /* Destination Cards */
    .destinations-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: auto auto;
        gap: 20px;
    }

    @media (max-width: 992px) {
        .destinations-grid { grid-template-columns: repeat(2, 1fr); }
        .dest-card-featured { grid-column: span 2; }
    }
    @media (max-width: 576px) {
        .destinations-grid { grid-template-columns: 1fr; }
        .dest-card-featured { grid-column: span 1; }
    }

    .dest-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        cursor: pointer;
        height: 280px;
        box-shadow: var(--shadow-soft);
        group: true;
    }

    .dest-card-featured {
        height: 380px;
    }

    .dest-card img {
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dest-card:hover img { transform: scale(1.08); }

    .dest-grad {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(5,10,30,0.92) 0%, rgba(0,0,0,0.1) 55%, transparent 100%);
        transition: background 0.4s ease;
    }

    .dest-card:hover .dest-grad {
        background: linear-gradient(to top, rgba(5,10,30,0.96) 0%, rgba(0,0,0,0.3) 60%, rgba(0,0,0,0.1) 100%);
    }

    .dest-info {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 28px;
        color: white;
        transform: translateY(0);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dest-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(59,130,246,0.85);
        backdrop-filter: blur(10px);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .dest-badge.hot { background: rgba(239,68,68,0.85); }
    .dest-badge.new { background: rgba(16,185,129,0.85); }

    .dest-info h4 {
        margin: 0 0 4px;
        font-weight: 800;
        font-size: 1.45rem;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .dest-price-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 8px;
    }

    .dest-price {
        font-size: 0.88rem;
        opacity: 0.8;
        font-weight: 500;
    }

    .dest-arrow {
        width: 36px; height: 36px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
        opacity: 0;
        transform: translateY(8px);
        transition: all 0.3s ease;
    }

    .dest-card:hover .dest-arrow {
        opacity: 1;
        transform: translateY(0);
    }

    /* Why Choose Us section */
    .why-section {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        padding: 100px 0;
        position: relative;
        overflow: hidden;
    }

    .why-section::before {
        content: '';
        position: absolute;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
        top: -200px; right: -200px;
    }

    .why-section::after {
        content: '';
        position: absolute;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
        bottom: -150px; left: -100px;
    }

    .why-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 20px;
        padding: 36px 32px;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .why-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(59,130,246,0.08), transparent);
        opacity: 0;
        transition: opacity 0.3s;
        border-radius: 20px;
    }

    .why-card:hover { transform: translateY(-6px); border-color: rgba(99,102,241,0.3); }
    .why-card:hover::before { opacity: 1; }

    .why-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(99,102,241,0.2));
        border: 1px solid rgba(99,102,241,0.3);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        color: #93c5fd;
        margin-bottom: 22px;
    }

    .why-card h5 {
        color: white;
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 10px;
    }

    .why-card p {
        color: rgba(255,255,255,0.55);
        font-size: 0.88rem;
        line-height: 1.7;
        margin: 0;
    }

    /* Airlines strip */
    .airlines-strip {
        padding: 60px 0;
        background: white;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        position: relative;
        z-index: 1;
    }

    .airlines-strip p {
        text-align: center;
        color: #94a3b8;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 28px;
    }

    .airline-logos {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 48px;
        flex-wrap: wrap;
    }

    .airline-logo-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #94a3b8;
        font-weight: 800;
        font-size: 0.95rem;
        letter-spacing: -0.3px;
        transition: color 0.2s;
        text-decoration: none;
    }

    .airline-logo-item:hover { color: #475569; }

    .airline-logo-item .al-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }

    /* Scroll reveal classes */
    .reveal-up {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal-up.visible { opacity: 1; transform: translateY(0); }

    /* Keyframes */
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(24px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-16px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .hero-section { padding-bottom: 220px; }
        .hero-stats { gap: 24px; }
        .search-inputs { flex-direction: column; }
        .s-field:first-child { border-bottom-left-radius: 0; border-top-right-radius: var(--input-radius); }
        .s-field { border-radius: 0; }
        .swap-btn { display: none; }
        .btn-search-main {
            border-radius: var(--input-radius) !important;
            width: 100%;
            justify-content: center;
            min-height: 56px;
            margin-top: 4px;
        }
        .search-cta-field { width: 100%; }
    }
</style>

<!-- ============================
     HERO SECTION
============================= -->
<section class="hero-section">
    <div class="hero-particles" id="heroParticles"></div>
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <div class="hero-badge">
            <span class="dot"></span>
            Best fares updated live
        </div>

        <h1 class="hero-title">
            Your Next Adventure<br>
            <span class="highlight">Starts Right Here</span>
        </h1>

        <p class="hero-subtitle">Search, compare, and book the cheapest flights instantly.<br>Millions of routes. One smart search engine.</p>

        <div class="hero-stats">
            <div class="hero-stat">
                <strong>500+</strong>
                <span>Airlines</span>
            </div>
            <div class="stat-divider"></div>
            <div class="hero-stat">
                <strong>2M+</strong>
                <span>Travelers</span>
            </div>
            <div class="stat-divider"></div>
            <div class="hero-stat">
                <strong>180+</strong>
                <span>Countries</span>
            </div>
        </div>
    </div>

    <!-- Search Panel -->
    <div class="search-panel-wrapper">
        <div class="search-panel-inner">
            <form action="/search" method="POST" id="flightSearchForm">
                @csrf

                <!-- Trip Type Tabs -->
                <div class="trip-tabs">
                    <button type="button" class="trip-tab active" data-value="one-way">
                        <i class="bi bi-arrow-right me-1"></i> One Way
                    </button>
                    <button type="button" class="trip-tab" data-value="round-way">
                        <i class="bi bi-arrow-left-right me-1"></i> Round Way
                    </button>
                    <button type="button" class="trip-tab" data-value="multi-city">
                        <i class="bi bi-plus-circle me-1"></i> Multi City
                    </button>
                    <input type="hidden" name="trip_type" id="tripTypeInput" value="one-way">
                </div>

                <!-- Inputs Row -->
                <div class="d-flex gap-0 align-items-stretch" style="border-radius: 14px; overflow: visible; border: 1.5px solid #e2e8f0;">
                    <div class="search-inputs" style="flex: 1; border: none; border-radius: 0; overflow: visible;">
                        <!-- Origin -->
                        <div class="s-field" style="flex: 1.2; min-width: 140px;">
                            <label><i class="bi bi-geo-alt me-1"></i>From</label>
                            <input type="text" name="from_location" id="fromInput" value="DAC" placeholder="City or airport" required list="airportsList" autocomplete="off">
                            <div class="s-sub" id="fromCityName">Dhaka, Bangladesh</div>
                            <div class="swap-btn" id="swapBtn" title="Swap airports">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Destination -->
                        <div class="s-field" style="flex: 1.2; min-width: 140px;">
                            <label><i class="bi bi-geo me-1"></i>To</label>
                            <input type="text" name="to_location" id="toInput" value="CXB" placeholder="City or airport" required list="airportsList" autocomplete="off">
                            <div class="s-sub" id="toCityName">Cox's Bazar, Bangladesh</div>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Departure -->
                        <div class="s-field" style="flex: 1; min-width: 130px; position: relative;">
                            <label><i class="bi bi-calendar3 me-1"></i>Departure</label>
                            <input type="date" name="departure_date" id="depDateInput" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                            <i class="cal-icon bi bi-calendar2-week"></i>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Return -->
                        <div class="s-field" id="returnBox" style="flex: 1; min-width: 130px; opacity: 0.38; transition: opacity 0.3s; position: relative;">
                            <label><i class="bi bi-calendar3 me-1"></i>Return</label>
                            <input type="date" name="return_date" id="returnDateInput" placeholder="Add return" disabled>
                            <i class="cal-icon bi bi-calendar2-week"></i>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Travelers & Class -->
                        <div class="s-field" id="travelerGroup" style="flex: 1.2; min-width: 150px; position: relative; cursor: pointer;">
                            <label><i class="bi bi-people me-1"></i>Travelers & Class</label>
                            <div id="travelerTrigger">
                                <div class="s-display" id="travelerDisplay" style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">1 Traveler</div>
                                <div class="s-sub" id="classDisplay">Economy</div>
                            </div>

                            <!-- Traveler Dropdown -->
                            <div class="traveler-dropdown" id="travelerDropdown">
                                <div class="t-row">
                                    <div class="t-info d-flex align-items-center gap-3">
                                        <i class="bi bi-person-fill fs-4 text-secondary"></i>
                                        <div><h6>Adults</h6><p>12 years & above</p></div>
                                    </div>
                                    <div class="t-controls">
                                        <button type="button" class="t-btn" data-type="adults" data-delta="-1"><i class="bi bi-dash"></i></button>
                                        <span id="adultsCount">1</span>
                                        <button type="button" class="t-btn" data-type="adults" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="t-row">
                                    <div class="t-info d-flex align-items-center gap-3">
                                        <i class="bi bi-person fs-4 text-secondary"></i>
                                        <div><h6>Children</h6><p>From 5 to under 12</p></div>
                                    </div>
                                    <div class="t-controls">
                                        <button type="button" class="t-btn" data-type="children" data-delta="-1"><i class="bi bi-dash"></i></button>
                                        <span id="childrenCount">0</span>
                                        <button type="button" class="t-btn" data-type="children" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>

                                <div class="t-row">
                                    <div class="t-info d-flex align-items-center gap-3">
                                        <i class="bi bi-emoji-smile fs-4 text-secondary"></i>
                                        <div><h6>Infants</h6><p>Under 2 years</p></div>
                                    </div>
                                    <div class="t-controls">
                                        <button type="button" class="t-btn" data-type="infants" data-delta="-1"><i class="bi bi-dash"></i></button>
                                        <span id="infantsCount">0</span>
                                        <button type="button" class="t-btn" data-type="infants" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>

                                <div class="cabin-section">
                                    <h6>Cabin Class</h6>
                                    <div class="cabin-options">
                                        <div class="cabin-chip active" data-class="Economy">Economy</div>
                                        <div class="cabin-chip" data-class="Business">Business</div>
                                        <div class="cabin-chip" data-class="First">First</div>
                                    </div>
                                </div>

                                <button type="button" class="btn-done-new" id="doneBtn">Done</button>

                                <input type="hidden" name="adults" id="adultsHidden" value="1">
                                <input type="hidden" name="children" id="childrenHidden" value="0">
                                <input type="hidden" name="infants" id="infantsHidden" value="0">
                                <input type="hidden" name="cabin_class" id="cabinHidden" value="Economy">
                            </div>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="search-cta-field">
                        <button type="submit" class="btn-search-main">
                            <i class="bi bi-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </div>

                <datalist id="airportsList"></datalist>
            </form>
        </div>
    </div>
</section>

<!-- ============================
     AIRLINES STRIP
============================= -->
<div class="airlines-strip">
    <div class="container">
        <p>Trusted by millions — We compare prices from top airlines</p>
        <div class="airline-logos">
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#f0f4ff; color:#1a56db;">✈</div>
                <span>Biman</span>
            </div>
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#fff7ed; color:#c2410c;">✈</div>
                <span>Emirates</span>
            </div>
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#f0fdf4; color:#16a34a;">✈</div>
                <span>US-Bangla</span>
            </div>
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#fdf4ff; color:#9333ea;">✈</div>
                <span>Qatar Airways</span>
            </div>
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#eff6ff; color:#2563eb;">✈</div>
                <span>IndiGo</span>
            </div>
            <div class="airline-logo-item">
                <div class="al-icon" style="background:#fff1f2; color:#e11d48;">✈</div>
                <span>Air Arabia</span>
            </div>
        </div>
    </div>
</div>

<!-- ============================
     POPULAR DESTINATIONS
============================= -->
<section class="py-5 my-4">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4 pb-2 reveal-up">
            <div>
                <div class="section-tag"><i class="bi bi-fire"></i> Top Picks</div>
                <h2 class="section-title">Popular Destinations</h2>
                <p class="section-subtitle">Explore trending routes with the best fares</p>
            </div>
            <a href="#" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-600" style="font-weight: 600; white-space: nowrap;">View all →</a>
        </div>

        <div class="destinations-grid">
            <!-- Featured card -->
            <div class="dest-card dest-card-featured reveal-up" style="transition-delay: 0.1s;">
                <img src="https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&w=900&q=80" alt="Paris, France" loading="lazy">
                <div class="dest-grad"></div>
                <div class="dest-info">
                    <div class="dest-badge hot"><i class="bi bi-fire"></i> Hot Deal</div>
                    <h4>Paris, France</h4>
                    <div class="dest-price-row">
                        <span class="dest-price">Flights from <strong>BDT 85,000</strong></span>
                        <div class="dest-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    </div>
                </div>
            </div>

            <!-- Top right -->
            <div class="dest-card reveal-up" style="transition-delay: 0.2s;">
                <img src="https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=800&q=80" alt="Tokyo, Japan" loading="lazy">
                <div class="dest-grad"></div>
                <div class="dest-info">
                    <div class="dest-badge"><i class="bi bi-star-fill"></i> Best Value</div>
                    <h4>Tokyo, Japan</h4>
                    <div class="dest-price-row">
                        <span class="dest-price">From <strong>BDT 1,20,000</strong></span>
                        <div class="dest-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    </div>
                </div>
            </div>

            <!-- Middle right -->
            <div class="dest-card reveal-up" style="transition-delay: 0.3s;">
                <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=800&q=80" alt="Dubai, UAE" loading="lazy">
                <div class="dest-grad"></div>
                <div class="dest-info">
                    <div class="dest-badge new"><i class="bi bi-lightning-fill"></i> Flash Sale</div>
                    <h4>Dubai, UAE</h4>
                    <div class="dest-price-row">
                        <span class="dest-price">From <strong>BDT 65,000</strong></span>
                        <div class="dest-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    </div>
                </div>
            </div>

            <!-- Bottom left -->
            <div class="dest-card reveal-up" style="transition-delay: 0.4s;">
                <img src="https://images.unsplash.com/photo-1524413840807-0c3cb6fa808d?auto=format&fit=crop&w=800&q=80" alt="Bangkok, Thailand" loading="lazy">
                <div class="dest-grad"></div>
                <div class="dest-info">
                    <h4>Bangkok, Thailand</h4>
                    <div class="dest-price-row">
                        <span class="dest-price">From <strong>BDT 55,000</strong></span>
                        <div class="dest-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    </div>
                </div>
            </div>

            <!-- Bottom middle -->
            <div class="dest-card reveal-up" style="transition-delay: 0.5s;">
                <img src="https://images.unsplash.com/photo-1580674684081-7617fbf3d745?auto=format&fit=crop&w=800&q=80" alt="Singapore" loading="lazy">
                <div class="dest-grad"></div>
                <div class="dest-info">
                    <h4>Singapore</h4>
                    <div class="dest-price-row">
                        <span class="dest-price">From <strong>BDT 72,000</strong></span>
                        <div class="dest-arrow"><i class="bi bi-arrow-up-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================
     WHY CHOOSE US
============================= -->
<section class="why-section">
    <div class="container position-relative" style="z-index: 2;">
        <div class="text-center mb-5 reveal-up">
            <div class="section-tag" style="background: rgba(99,102,241,0.15); color: #a5b4fc;"><i class="bi bi-shield-check"></i> Why AirTicket</div>
            <h2 class="section-title" style="color: white;">The smartest way to fly</h2>
            <p class="section-subtitle" style="color: rgba(255,255,255,0.55);">We compare hundreds of airlines so you always get the best deal</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal-up" style="transition-delay: 0.1s;">
                <div class="why-card">
                    <div class="why-icon"><i class="bi bi-search-heart"></i></div>
                    <h5>Smart Search</h5>
                    <p>Our AI compares millions of price combinations to surface the cheapest options instantly.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal-up" style="transition-delay: 0.2s;">
                <div class="why-card">
                    <div class="why-icon" style="background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.3); color: #6ee7b7;"><i class="bi bi-currency-dollar"></i></div>
                    <h5>Best Price Guarantee</h5>
                    <p>Found a cheaper flight elsewhere? We'll match it or give you the difference as credit.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal-up" style="transition-delay: 0.3s;">
                <div class="why-card">
                    <div class="why-icon" style="background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.3); color: #fcd34d;"><i class="bi bi-lightning-charge"></i></div>
                    <h5>Instant Booking</h5>
                    <p>Book in under 3 minutes with secure, instant confirmation across all major airlines.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal-up" style="transition-delay: 0.4s;">
                <div class="why-card">
                    <div class="why-icon" style="background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.3); color: #fca5a5;"><i class="bi bi-headset"></i></div>
                    <h5>24/7 Support</h5>
                    <p>Our travel experts are always on standby to assist you before, during, and after your trip.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // ===== HERO PARTICLES =====
    (function() {
        const container = document.getElementById('heroParticles');
        for (let i = 0; i < 25; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.cssText = `
                left: ${Math.random() * 100}%;
                width: ${Math.random() * 3 + 1}px;
                height: ${Math.random() * 3 + 1}px;
                animation-duration: ${Math.random() * 12 + 8}s;
                animation-delay: ${Math.random() * 10}s;
                opacity: ${Math.random() * 0.6 + 0.1};
            `;
            container.appendChild(p);
        }
    })();

    // ===== TRAVELER COUNTS =====
    let counts = { adults: 1, children: 0, infants: 0 };
    let selectedCabin = 'Economy';

    function refreshTravelerDisplay() {
        const total = counts.adults + counts.children + counts.infants;
        document.getElementById('travelerDisplay').textContent = `${total} Traveler${total !== 1 ? 's' : ''}`;
        document.getElementById('classDisplay').textContent = selectedCabin;
    }

    document.addEventListener('DOMContentLoaded', async function() {
        // --- Traveler dropdown toggle ---
        const travelerGroup = document.getElementById('travelerGroup');
        const trigger = document.getElementById('travelerTrigger');
        const dropdown = document.getElementById('travelerDropdown');
        const doneBtn = document.getElementById('doneBtn');

        // Open/close dropdown when clicking the field (but NOT when clicking inside the dropdown)
        travelerGroup.addEventListener('click', e => { 
            e.stopPropagation();
            // If the click is inside the dropdown itself, don't toggle
            if (dropdown.contains(e.target)) return;
            dropdown.classList.toggle('show'); 
        });
        // Prevent clicks inside dropdown from bubbling to travelerGroup
        dropdown.addEventListener('click', e => {
            e.stopPropagation();
        });
        doneBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.remove('show');
        });
        document.addEventListener('click', e => {
            if (!dropdown.contains(e.target) && !travelerGroup.contains(e.target)) dropdown.classList.remove('show');
        });

        // --- Counter buttons ---
        document.querySelectorAll('.t-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                const delta = parseInt(btn.dataset.delta);
                if (type === 'adults' && counts.adults + delta < 1) return;
                if (counts[type] + delta < 0) return;
                counts[type] += delta;
                document.getElementById(`${type}Count`).textContent = counts[type];
                document.getElementById(`${type}Hidden`).value = counts[type];
                refreshTravelerDisplay();
            });
        });

        // --- Cabin chips ---
        document.querySelectorAll('.cabin-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.cabin-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                selectedCabin = chip.dataset.class;
                document.getElementById('cabinHidden').value = selectedCabin;
                refreshTravelerDisplay();
            });
        });

        // --- Trip type tabs ---
        const tabs = document.querySelectorAll('.trip-tab');
        const tripTypeInput = document.getElementById('tripTypeInput');
        const returnBox = document.getElementById('returnBox');
        const returnDateInput = document.getElementById('returnDateInput');
        const depInput = document.getElementById('depDateInput');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const val = tab.dataset.value;
                tripTypeInput.value = val;
                if (val === 'round-way') {
                    returnBox.style.opacity = '1';
                    returnDateInput.disabled = false;
                    if (!returnDateInput.value && depInput.value) {
                        const d = new Date(depInput.value);
                        d.setDate(d.getDate() + 4);
                        returnDateInput.value = d.toISOString().split('T')[0];
                    }
                } else {
                    returnBox.style.opacity = '0.38';
                    returnDateInput.disabled = true;
                    returnDateInput.value = '';
                }
            });
        });

        // --- Swap airports ---
        document.getElementById('swapBtn').addEventListener('click', () => {
            const from = document.getElementById('fromInput');
            const to = document.getElementById('toInput');
            const fromCity = document.getElementById('fromCityName');
            const toCity = document.getElementById('toCityName');

            [from.value, to.value] = [to.value, from.value];
            [fromCity.textContent, toCity.textContent] = [toCity.textContent, fromCity.textContent];

            // Animate swap
            document.getElementById('swapBtn').style.transform = 'translateY(-50%) rotate(360deg)';
            setTimeout(() => { document.getElementById('swapBtn').style.transform = 'translateY(-50%) rotate(0deg)'; }, 400);
        });

        // --- Airport autocomplete ---
        try {
            const res = await fetch('/airports_search.json');
            const data = await res.json();
            const dl = document.getElementById('airportsList');
            data.forEach(a => {
                if (a.code) {
                    const opt = document.createElement('option');
                    opt.value = a.code;
                    opt.textContent = `${a.name} (${a.city})`;
                    dl.appendChild(opt);
                }
            });
        } catch(e) {}

        // --- Scroll reveal ---
        const revealEls = document.querySelectorAll('.reveal-up');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => observer.observe(el));

    });
</script>
@endsection
