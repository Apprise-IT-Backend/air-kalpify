@extends('layouts.master')

@section('content')

<style>
/* ─── Page Base ─────────────────────────────────────────── */
:root {
    --blue: #3b82f6;
    --indigo: #6366f1;
    --blue-dark: #1d4ed8;
    --green: #10b981;
    --amber: #f59e0b;
    --red: #ef4444;
    --surface: #ffffff;
    --surface-2: #f8fafc;
    --border: #e2e8f0; /* Matched to home page */
    --text-1: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --text-primary: #0f172a;
    --text-secondary: #64748b;
    --radius: 16px;
    --card-radius: 20px;
    --input-radius: 14px;
    --shadow-sm: 0 2px 12px rgba(0,0,0,0.06);
    --shadow-md: 0 6px 30px rgba(0,0,0,0.10);
    --shadow-soft: 0 4px 24px rgba(0,0,0,0.06);
    --shadow-med: 0 8px 40px rgba(0,0,0,0.12);
    --shadow-strong: 0 20px 60px rgba(0,0,0,0.2);
}

body { background: #f1f5f9 !important; }

/* ─── Search Summary Bar ────────────────────────────────── */
.results-bar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0;
    position: sticky;
    top: 0;
    z-index: 900;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}

.results-bar-inner {
    display: flex;
    align-items: center;
    gap: 0;
    min-height: 64px;
}

.route-info {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 24px;
    flex: 1;
}

.route-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #eff6ff, #e0e7ff);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--blue);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.route-from-to {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.route-arrow {
    width: 28px; height: 28px;
    background: #eff6ff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--blue);
    font-size: 0.75rem;
}

.route-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 4px;
    flex-wrap: wrap;
}

.route-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--text-2);
    font-size: 0.8rem;
    font-weight: 500;
}

.route-meta-chip i { color: var(--text-3); }

.bar-divider { width: 1px; background: var(--border); align-self: stretch; flex-shrink: 0; }

.modify-btn-wrap {
    padding: 14px 20px;
    flex-shrink: 0;
}

.btn-modify {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    color: var(--text-2);
    padding: 9px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-modify:hover {
    background: #eff6ff;
    border-color: var(--blue);
    color: var(--blue);
}

/* Collapsible modify search panel */
.modify-search-panel {
    background: #f8fafc;
    border-top: 1px solid var(--border);
    padding: 30px 0;
    display: none;
    box-shadow: inset 0 10px 20px -10px rgba(0,0,0,0.05);
}
.modify-search-panel.open { display: block; }

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
    background: white;
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

/* Airport Results Dropdown */
.airport-results {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    width: 320px;
    background: white;
    border-radius: 18px;
    box-shadow: var(--shadow-strong);
    z-index: 1100;
    display: none;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid var(--border);
    padding: 8px;
    animation: dropIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.airport-results.show { display: block; }

.airport-item {
    padding: 12px 16px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.2s;
}

.airport-item:hover {
    background: #f1f5f9;
}

.airport-icon {
    width: 36px;
    height: 36px;
    background: #eff6ff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--blue);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.airport-info {
    flex: 1;
    min-width: 0;
}

.airport-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.airport-name {
    font-weight: 700;
    font-size: 0.92rem;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.airport-code {
    font-weight: 800;
    font-size: 0.8rem;
    color: var(--blue);
    background: #eff6ff;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}

.airport-sub {
    font-size: 0.75rem;
    color: var(--text-secondary);
    margin-top: 2px;
}

@media (max-width: 768px) {
    .airport-results { width: 100%; right: 0; }
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
}

/* ─── Layout ────────────────────────────────────────────── */
.results-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 20px;
    max-width: 1280px;
    margin: 24px auto;
    padding: 0 20px 60px;
}

@media (max-width: 992px) {
    .results-layout {
        grid-template-columns: 1fr;
    }
    .sidebar { order: 2; }
    .results-col { order: 1; }
}

/* ─── Sidebar ───────────────────────────────────────────── */
.sidebar { display: flex; flex-direction: column; gap: 16px; }

.filter-card {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 22px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
}

.filter-card h6 {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-3);
    margin-bottom: 16px;
}

/* Stop chips */
.stop-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.stop-chip {
    padding: 6px 14px;
    border-radius: 20px;
    border: 1.5px solid var(--border);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-2);
    background: var(--surface);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
}
.stop-chip input[type=checkbox] {
    position: absolute; opacity: 0; width: 0; height: 0;
}
.stop-chip.chip-active {
    background: #eff6ff;
    border-color: var(--blue);
    color: var(--blue);
}

/* OTA filter items */
.ota-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.15s;
}
.ota-item:hover { background: #f8fafc; }
.ota-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.ota-name { font-size: 0.85rem; font-weight: 600; color: var(--text-1); flex: 1; }
.ota-count { font-size: 0.75rem; font-weight: 600; color: var(--text-3); background: #f1f5f9; padding: 2px 8px; border-radius: 10px; }

/* Price range */
.price-range-wrap input[type=range] {
    -webkit-appearance: none;
    width: 100%;
    height: 4px;
    background: #e2e8f0;
    border-radius: 4px;
    outline: none;
    margin: 12px 0 8px;
}
.price-range-wrap input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px; height: 18px;
    background: var(--blue);
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(59,130,246,0.4);
    cursor: pointer;
}
.price-display {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--blue);
}

/* Alert card */
.alert-card {
    background: linear-gradient(135deg, #0f172a, #1e1b4b);
    border-radius: var(--radius);
    padding: 22px;
    text-align: center;
    color: white;
}
.alert-card .al-icon {
    width: 48px; height: 48px;
    background: rgba(251,191,36,0.15);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    color: #fbbf24;
    margin: 0 auto 14px;
}
.alert-card h6 { font-weight: 700; color: white; margin-bottom: 6px; }
.alert-card p { font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-bottom: 16px; }
.btn-set-alert {
    background: rgba(251,191,36,0.15);
    border: 1px solid rgba(251,191,36,0.35);
    color: #fbbf24;
    padding: 9px 22px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
}
.btn-set-alert:hover { background: rgba(251,191,36,0.25); }

/* ─── Results Column ─────────────────────────────────────── */
.results-col { display: flex; flex-direction: column; gap: 12px; }

/* Status banner */
.status-banner {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    gap: 12px;
    flex-wrap: wrap;
}

.status-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-count {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-1);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: var(--blue);
}

.status-badge.done {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: var(--green);
}

/* Sort buttons */
.sort-tabs {
    display: flex;
    gap: 6px;
}

.sort-tab {
    padding: 7px 16px;
    border-radius: 20px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-2);
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.sort-tab.active, .sort-tab:hover {
    background: #eff6ff;
    border-color: var(--blue);
    color: var(--blue);
}

/* Progress bar */
.search-progress {
    height: 5px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    flex: 1;
    min-width: 80px;
}
.search-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--blue), var(--indigo));
    border-radius: 10px;
    transition: width 0.5s ease;
    width: 0%;
}

/* ─── Skeleton ──────────────────────────────────────────── */
.skeleton-card {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 24px;
    border: 1px solid var(--border);
}
.skel {
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 400% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 6px;
}
.skel-h { height: 14px; margin-bottom: 10px; }
.skel-sm { height: 10px; }
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ─── Flight Card ───────────────────────────────────────── */
.flight-card {
    background: var(--surface);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.25s, transform 0.25s;
    cursor: pointer;
    animation: cardIn 0.35s ease both;
}
.flight-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
    border-color: #bfdbfe;
}
@keyframes cardIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.flight-card-inner {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0;
}

/* Left: flight leg(s) */
.flight-legs {
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* Each leg row */
.leg-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 0;
}
.leg-row + .leg-row {
    border-top: 1px dashed #e2e8f0;
    margin-top: 4px;
}

/* Airline cell — fixed width so times are always aligned */
.airline-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 160px;
    min-width: 160px;
    max-width: 160px;
    flex-shrink: 0;
    overflow: hidden;
}
.airline-logo-wrap {
    width: 40px; height: 40px;
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.airline-logo-wrap img { width: 32px; height: 32px; object-fit: contain; }
.airline-initial {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 1rem;
}
.airline-name-wrap { min-width: 0; overflow: hidden; }
.airline-name-wrap .airline-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-1);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.airline-name-wrap .leg-badge {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 2px 7px;
    border-radius: 8px;
    display: inline-block;
    margin-top: 3px;
    text-transform: uppercase;
}
.leg-badge.depart { background: #eff6ff; color: var(--blue); }
.leg-badge.return { background: #f0fdf4; color: var(--green); }

/* Times cell */
.times-cell {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0;
}
.time-block { text-align: center; }
.time-block .time { font-size: 1.2rem; font-weight: 800; color: var(--text-1); line-height: 1; }
.time-block .code { font-size: 0.7rem; font-weight: 700; color: var(--text-3); letter-spacing: 0.5px; margin-top: 3px; }

.flight-path {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 16px;
    position: relative;
}
.flight-path .duration { font-size: 0.72rem; font-weight: 600; color: var(--text-3); margin-bottom: 5px; }
.flight-line {
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, var(--blue), var(--indigo));
    border-radius: 2px;
    position: relative;
}
.flight-line::before,
.flight-line::after {
    content: '';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 7px; height: 7px;
    background: var(--blue);
    border: 2px solid white;
    border-radius: 50%;
    box-shadow: 0 0 0 1.5px var(--blue);
}
.flight-line::before { left: 0; }
.flight-line::after { right: 0; }
.flight-path .stops { font-size: 0.7rem; font-weight: 600; color: var(--text-3); margin-top: 5px; }
.flight-path .stops.direct { color: var(--green); }
.flight-path .stops.has-stops { color: var(--amber); }

/* Price cell */
.flight-price-col {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    padding: 20px 22px;
    background: #fafcff;
    border-left: 1px solid var(--border);
    min-width: 220px;
    text-align: right;
    gap: 10px;
}

.cheapest-offer-block {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.other-offers-container {
    border-top: 1px dashed var(--border);
    padding-top: 12px;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.other-offer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: all 0.15s;
}

.other-offer-row:hover {
    border-color: var(--blue);
    background: #f8fafc;
    transform: translateY(-1px);
}

.o-off-left {
    display: flex;
    align-items: center;
    gap: 6px;
}

.o-off-dot {
    width: 8px; height: 8px; border-radius: 50%;
}

.o-off-name { font-size: 0.75rem; font-weight: 700; color: var(--text-1); }
.o-off-price { font-size: 0.8rem; font-weight: 800; color: var(--blue); }
.price-from { font-size: 0.72rem; color: var(--text-3); font-weight: 500; }
.price-amount {
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--text-1);
    letter-spacing: -1px;
    line-height: 1;
}
.price-currency { font-size: 0.75rem; font-weight: 600; color: var(--text-3); vertical-align: super; margin-right: 2px; }
.provider-tag {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 8px;
    display: inline-block;
    color: white;
}
.btn-book {
    width: 100%;
    padding: 11px 0;
    border-radius: 10px;
    border: none;
    font-weight: 700;
    font-size: 0.85rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-book:hover { filter: brightness(1.1); transform: translateY(-1px); }

@media (max-width: 768px) {
    .flight-card-inner { grid-template-columns: 1fr; }
    .flight-price-col { border-left: none; border-top: 1px solid var(--border); min-width: unset; align-items: center; text-align: center; }
    .airline-cell { min-width: unset; }
}

/* ─── No Results ─────────────────────────────────────────── */
.no-results-card {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 60px 40px;
    text-align: center;
    border: 1px solid var(--border);
}
.no-results-card i { font-size: 3rem; color: var(--text-3); margin-bottom: 16px; }
.no-results-card h5 { font-weight: 700; color: var(--text-1); margin-bottom: 8px; }
.no-results-card p { color: var(--text-2); font-size: 0.9rem; }

/* ─── Modal ─────────────────────────────────────────────── */
.modal-content { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.25); }
.modal-header-custom {
    background: linear-gradient(135deg, #0f172a, #1e1b4b);
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-header-custom h5 { color: white; font-weight: 700; margin: 0; }
.modal-body-custom { padding: 28px; }
</style>

{{-- ─── Search Summary Bar ──────────────────────────── --}}
<div class="results-bar">
    <div class="results-bar-inner container-xl px-0">
        <div class="route-info">
            <div class="route-icon">
                <i class="bi bi-airplane-fill"></i>
            </div>
            <div>
                <div class="route-from-to">
                    <span>{{ $searchData['from_location'] }}</span>
                    <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                    <span>{{ $searchData['to_location'] }}</span>
                </div>
                <div class="route-meta mt-1">
                    <span class="route-meta-chip">
                        <i class="bi bi-calendar3"></i>
                        {{ \Carbon\Carbon::parse($searchData['departure_date'])->format('d M Y') }}
                        @if(!empty($searchData['return_date']))
                            &rarr; {{ \Carbon\Carbon::parse($searchData['return_date'])->format('d M Y') }}
                        @endif
                    </span>
                    <span class="route-meta-chip">
                        <i class="bi bi-people"></i>
                        {{ $searchData['adults'] ?? $searchData['passengers'] }}A
                        @if(($searchData['children'] ?? 0) > 0), {{ $searchData['children'] }}C @endif
                        @if(($searchData['kids'] ?? 0) > 0), {{ $searchData['kids'] }}K @endif
                        @if(($searchData['infants'] ?? 0) > 0), {{ $searchData['infants'] }}I @endif
                    </span>
                    <span class="route-meta-chip">
                        <i class="bi bi-grid"></i>
                        {{ $searchData['cabin_class'] ?? 'Economy' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bar-divider"></div>
        <div class="modify-btn-wrap">
            <button class="btn-modify" id="modifyToggle">
                <i class="bi bi-sliders"></i> Modify Search
            </button>
        </div>
    </div>

    {{-- Modify Search Inline Panel --}}
    <div class="modify-search-panel" id="modifyPanel">
        <div class="container-xl">
            <form action="/search" method="POST" id="modifySearchForm">
                @csrf
                
                <!-- Trip Type Tabs -->
                <div class="trip-tabs">
                    <button type="button" class="trip-tab {{ ($searchData['trip_type'] ?? 'one-way') == 'one-way' ? 'active' : '' }}" data-value="one-way">
                        <i class="bi bi-arrow-right me-1"></i> One Way
                    </button>
                    <button type="button" class="trip-tab {{ ($searchData['trip_type'] ?? '') == 'round-way' ? 'active' : '' }}" data-value="round-way">
                        <i class="bi bi-arrow-left-right me-1"></i> Round Way
                    </button>
                    <button type="button" class="trip-tab {{ ($searchData['trip_type'] ?? '') == 'multi-city' ? 'active' : '' }}" data-value="multi-city">
                        <i class="bi bi-plus-circle me-1"></i> Multi City
                    </button>
                    <input type="hidden" name="trip_type" id="tripTypeInput" value="{{ $searchData['trip_type'] ?? 'one-way' }}">
                </div>

                <div class="d-flex gap-0 align-items-stretch" style="border-radius: 14px; overflow: visible; border: 1.5px solid #e2e8f0;">
                    <div class="search-inputs" style="flex: 1; border: none; border-radius: 0; overflow: visible;">
                        <!-- Origin -->
                        <div class="s-field" style="flex: 1.2; min-width: 140px;">
                            <label><i class="bi bi-geo-alt me-1"></i>From</label>
                            <input type="text" name="from_location" id="fromInput" value="{{ $searchData['from_location'] }}" placeholder="City or airport" required autocomplete="off">
                            <div class="s-sub" id="fromCityName">Departure City</div>
                            <div class="swap-btn" id="swapBtn" title="Swap airports">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                            <div class="airport-results" id="fromResults"></div>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Destination -->
                        <div class="s-field" style="flex: 1.2; min-width: 140px;">
                            <label><i class="bi bi-geo me-1"></i>To</label>
                            <input type="text" name="to_location" id="toInput" value="{{ $searchData['to_location'] }}" placeholder="City or airport" required autocomplete="off">
                            <div class="s-sub" id="toCityName">Arrival City</div>
                            <div class="airport-results" id="toResults"></div>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Departure -->
                        <div class="s-field" style="flex: 1; min-width: 130px; position: relative;">
                            <label><i class="bi bi-calendar3 me-1"></i>Departure</label>
                            <input type="date" name="departure_date" id="depDateInput" value="{{ $searchData['departure_date'] }}" required>
                            <i class="cal-icon bi bi-calendar2-week"></i>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Return -->
                        <div class="s-field" id="returnBox" style="flex: 1; min-width: 130px; opacity: {{ ($searchData['trip_type'] ?? '') == 'round-way' ? '1' : '0.38' }}; transition: opacity 0.3s; position: relative;">
                            <label><i class="bi bi-calendar3 me-1"></i>Return</label>
                            <input type="date" name="return_date" id="returnDateInput" value="{{ $searchData['return_date'] ?? '' }}" placeholder="Add return" {{ ($searchData['trip_type'] ?? '') == 'round-way' ? '' : 'disabled' }}>
                            <i class="cal-icon bi bi-calendar2-week"></i>
                        </div>

                        <div class="s-separator"></div>

                        <!-- Travelers & Class -->
                        <div class="s-field" id="travelerGroup" style="flex: 1.2; min-width: 150px; position: relative; cursor: pointer;">
                            <label><i class="bi bi-people me-1"></i>Travelers & Class</label>
                            <div id="travelerTrigger">
                                @php
                                    $totalTravelers = ($searchData['adults'] ?? 1) + ($searchData['children'] ?? 0) + ($searchData['kids'] ?? 0) + ($searchData['infants'] ?? 0);
                                @endphp
                                <div class="s-display" id="travelerDisplay" style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">{{ $totalTravelers }} Traveler{{ $totalTravelers > 1 ? 's' : '' }}</div>
                                <div class="s-sub" id="classDisplay">{{ $searchData['cabin_class'] ?? 'Economy' }}</div>
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
                                        <span id="adultsCount">{{ $searchData['adults'] ?? 1 }}</span>
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
                                        <span id="childrenCount">{{ $searchData['children'] ?? 0 }}</span>
                                        <button type="button" class="t-btn" data-type="children" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="t-row">
                                    <div class="t-info d-flex align-items-center gap-3">
                                        <i class="bi bi-person-heart fs-4 text-secondary"></i>
                                        <div><h6>Kids</h6><p>From 2 to under 5</p></div>
                                    </div>
                                    <div class="t-controls">
                                        <button type="button" class="t-btn" data-type="kids" data-delta="-1"><i class="bi bi-dash"></i></button>
                                        <span id="kidsCount">{{ $searchData['kids'] ?? 0 }}</span>
                                        <button type="button" class="t-btn" data-type="kids" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                                <div class="t-row">
                                    <div class="t-info d-flex align-items-center gap-3">
                                        <i class="bi bi-emoji-smile fs-4 text-secondary"></i>
                                        <div><h6>Infants</h6><p>Under 2 years</p></div>
                                    </div>
                                    <div class="t-controls">
                                        <button type="button" class="t-btn" data-type="infants" data-delta="-1"><i class="bi bi-dash"></i></button>
                                        <span id="infantsCount">{{ $searchData['infants'] ?? 0 }}</span>
                                        <button type="button" class="t-btn" data-type="infants" data-delta="1"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>

                                <div class="cabin-section">
                                    <h6>Cabin Class</h6>
                                    <div class="cabin-options">
                                        <div class="cabin-chip {{ ($searchData['cabin_class'] ?? 'Economy') == 'Economy' ? 'active' : '' }}" data-class="Economy">Economy</div>
                                        <div class="cabin-chip {{ ($searchData['cabin_class'] ?? '') == 'Business' ? 'active' : '' }}" data-class="Business">Business</div>
                                        <div class="cabin-chip {{ ($searchData['cabin_class'] ?? '') == 'First' ? 'active' : '' }}" data-class="First">First</div>
                                    </div>
                                </div>

                                <button type="button" class="btn-done-new" id="doneBtn">Done</button>

                                <input type="hidden" name="adults" id="adultsHidden" value="{{ $searchData['adults'] ?? 1 }}">
                                <input type="hidden" name="children" id="childrenHidden" value="{{ $searchData['children'] ?? 0 }}">
                                <input type="hidden" name="kids" id="kidsHidden" value="{{ $searchData['kids'] ?? 0 }}">
                                <input type="hidden" name="infants" id="infantsHidden" value="{{ $searchData['infants'] ?? 0 }}">
                                <input type="hidden" name="cabin_class" id="cabinHidden" value="{{ $searchData['cabin_class'] ?? 'Economy' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="search-cta-field">
                        <button type="submit" class="btn-search-main" id="modifySubmitBtn">
                            <i class="bi bi-search"></i>
                            <span>Search</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Main Content ────────────────────────────────── --}}
<div class="results-layout">

    {{-- Sidebar --}}
    <aside class="sidebar">

        {{-- Stops filter --}}
        <div class="filter-card">
            <h6><i class="bi bi-funnel me-2"></i>Stops</h6>
            <div class="stop-chips">
                <label class="stop-chip chip-active" id="chipDirect">
                    <input class="filter-trigger" type="checkbox" id="direct" value="0" checked>
                    <i class="bi bi-circle-fill" style="font-size:6px; color: var(--green);"></i> Direct
                </label>
                <label class="stop-chip chip-active" id="chipOneStop">
                    <input class="filter-trigger" type="checkbox" id="onePlusStop" value="1+" checked>
                    <i class="bi bi-circle-fill" style="font-size:6px; color: var(--amber);"></i> 1+ Stop
                </label>
            </div>
        </div>

        {{-- OTA filter --}}
        <div class="filter-card">
            <h6><i class="bi bi-shop me-2"></i>Booking Source</h6>
            <div id="otaFiltersContainer">
                <div style="color: var(--text-3); font-size: 0.82rem; padding: 8px 0;">
                    <div class="skel skel-h w-75 mb-3" style="width:75%"></div>
                    <div class="skel skel-h" style="width:50%"></div>
                </div>
            </div>
        </div>

        {{-- Price filter --}}
        <div class="filter-card">
            <h6><i class="bi bi-tag me-2"></i>Max Price</h6>
            <div class="price-range-wrap">
                <div class="d-flex justify-content-between align-items-baseline mb-1">
                    <span style="font-size:0.75rem; color: var(--text-3);">BDT</span>
                    <span class="price-display" id="priceValue">1,50,000</span>
                </div>
                <input type="range" min="1000" max="150000" step="500" id="priceRange" value="150000">
                <div class="d-flex justify-content-between" style="font-size:0.72rem; color: var(--text-3);">
                    <span>1,000</span><span>1,50,000</span>
                </div>
            </div>
        </div>

        {{-- Price alert CTA --}}
        <div class="alert-card">
            <div class="al-icon"><i class="bi bi-bell-fill"></i></div>
            <h6>Price Drop Alert</h6>
            <p>Get notified when fares drop for this route.</p>
            <button class="btn-set-alert" data-bs-toggle="modal" data-bs-target="#priceAlertModal">
                <i class="bi bi-bell me-2"></i>Set Alert
            </button>
        </div>

    </aside>

    {{-- Results col --}}
    <div class="results-col">

        {{-- Status + Sort bar --}}
        <div class="status-banner">
            <div class="status-left">
                <span class="status-count" id="resultsCountHeader">Searching flights…</span>
                <div class="search-progress">
                    <div class="search-progress-fill" id="searchProgressBar"></div>
                </div>
                <span class="status-badge" id="loaderIcon">
                    <span class="spinner-border spinner-border-sm" role="status" style="width:11px;height:11px;border-width:2px;"></span>
                    <span id="loaderText">Fetching</span>
                </span>
            </div>
            <div class="sort-tabs" id="sortTabs">
                <button class="sort-tab active" data-sort="price_asc"><i class="bi bi-currency-dollar"></i> Cheapest</button>
                <button class="sort-tab" data-sort="duration_asc"><i class="bi bi-lightning"></i> Fastest</button>
                <button class="sort-tab" data-sort="best_score"><i class="bi bi-star"></i> Best</button>
            </div>
        </div>

        {{-- Flight list --}}
        <div id="flightList">
            @for($i = 0; $i < 4; $i++)
            <div class="skeleton-card">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="skel" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
                    <div style="flex:1">
                        <div class="skel skel-h" style="width:40%;"></div>
                        <div class="skel skel-sm" style="width:25%;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between gap-4">
                    <div style="flex:1"><div class="skel skel-h" style="width:30%;"></div></div>
                    <div style="flex:2"><div class="skel" style="height:4px;"></div></div>
                    <div style="flex:1"><div class="skel skel-h" style="width:30%;margin-left:auto;"></div></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- No results --}}
        <div id="noResults" class="no-results-card d-none">
            <i class="bi bi-airplane d-block mb-3"></i>
            <h5>No Flights Found</h5>
            <p>Try adjusting your filters or search for a different date.</p>
            <button class="btn btn-primary rounded-pill px-4 fw-bold mt-2" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-2"></i>Retry
            </button>
        </div>

    </div>{{-- /.results-col --}}
</div>{{-- /.results-layout --}}

{{-- ─── Flight Card Template ────────────────────────── --}}
<template id="flightCardTemplate">
    <div class="flight-card">
        <div class="flight-card-inner">
            <div class="flight-legs">
                <div class="departure-leg-container"></div>
                <div class="return-leg-container d-none"></div>
            </div>
            <div class="flight-price-col">
                <div class="cheapest-offer-block">
                    <div>
                        <div class="price-from text-end">Best Price</div>
                        <div class="price-amount text-end">
                            <span class="price-currency currency-label"></span><span class="price-value"></span>
                        </div>
                    </div>
                    <span class="provider-tag provider-label"></span>
                    <button class="btn-book select-btn">
                        Book Now <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
                <div class="other-offers-container d-none"></div>
            </div>
        </div>
    </div>
</template>

{{-- ─── Leg Row Template ────────────────────────────── --}}
<template id="legTemplate">
    <div class="leg-row">
        <div class="airline-cell">
            <div class="airline-logo-wrap logo-wrap-inner"></div>
            <div class="airline-name-wrap">
                <div class="airline-name"></div>
                <span class="leg-badge"></span>
            </div>
        </div>
        <div class="times-cell">
            <div class="time-block">
                <div class="time dep-time"></div>
                <div class="code origin-code"></div>
            </div>
            <div class="flight-path flex-grow-1">
                <div class="duration duration-label"></div>
                <div class="flight-line"></div>
                <div class="stops stops-label"></div>
            </div>
            <div class="time-block">
                <div class="time arr-time"></div>
                <div class="code dest-code"></div>
            </div>
        </div>
    </div>
</template>

{{-- ─── Price Alert Modal ───────────────────────────── --}}
<div class="modal fade" id="priceAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-custom">
                <h5><i class="bi bi-bell-fill me-2" style="color:#fbbf24"></i>Create Price Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/alerts" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <p style="color: var(--text-2); font-size: 0.9rem; margin-bottom: 20px;">
                        We'll notify you when prices drop for
                        <strong>{{ $searchData['from_location'] }}</strong> &rarr; <strong>{{ $searchData['to_location'] }}</strong>.
                    </p>
                    <input type="hidden" name="from_location" value="{{ $searchData['from_location'] }}">
                    <input type="hidden" name="to_location"   value="{{ $searchData['to_location'] }}">
                    <input type="hidden" name="departure_date" value="{{ $searchData['departure_date'] }}">
                    <input type="hidden" name="return_date"    value="{{ $searchData['return_date'] ?? '' }}">
                    <input type="hidden" name="passengers"     value="{{ $searchData['passengers'] }}">
                    <div class="mb-0">
                        <label class="form-label fw-semibold mb-2">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control border-start-0" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                </div>
                <div class="px-4 pb-4 d-flex gap-3 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const providers = @json($providers);
    let allFlights = [];
    let completedProviders = new Set();
    let providerState = {};
    let currentSort = 'price_asc';

    const flightList = $('flightList');
    const searchProgressBar = $('searchProgressBar');
    const otaFiltersContainer = $('otaFiltersContainer');
    const priceRange = $('priceRange');
    const priceValue = $('priceValue');
    const noResults = $('noResults');
    const resultsCountHeader = $('resultsCountHeader');
    const loaderText = $('loaderText');
    const loaderIcon = $('loaderIcon');

    function $(id) { return document.getElementById(id); }

    // --- Traveler Counts Initialization ---
    let counts = { 
        adults: parseInt(@json($searchData['adults'] ?? 1)), 
        children: parseInt(@json($searchData['children'] ?? 0)), 
        kids: parseInt(@json($searchData['kids'] ?? 0)), 
        infants: parseInt(@json($searchData['infants'] ?? 0)) 
    };
    let selectedCabin = @json($searchData['cabin_class'] ?? 'Economy');

    function refreshTravelerDisplay() {
        const total = counts.adults + counts.children + counts.kids + counts.infants;
        $('travelerDisplay').textContent = `${total} Traveler${total !== 1 ? 's' : ''}`;
        $('classDisplay').textContent = selectedCabin;
    }

    // --- Traveler dropdown toggle ---
    const travelerGroup = $('travelerGroup');
    const dropdown = $('travelerDropdown');
    const doneBtn = $('doneBtn');

    travelerGroup.addEventListener('click', e => { 
        e.stopPropagation();
        if (dropdown.contains(e.target)) return;
        dropdown.classList.toggle('show'); 
    });
    dropdown.addEventListener('click', e => e.stopPropagation());
    doneBtn.addEventListener('click', e => {
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
            $(`${type}Count`).textContent = counts[type];
            $(`${type}Hidden`).value = counts[type];
            refreshTravelerDisplay();
        });
    });

    // --- Cabin chips ---
    document.querySelectorAll('.cabin-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.cabin-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            selectedCabin = chip.dataset.class;
            $('cabinHidden').value = selectedCabin;
            refreshTravelerDisplay();
        });
    });

    // --- Trip type tabs ---
    const tabs = document.querySelectorAll('.trip-tab');
    const tripTypeInput = $('tripTypeInput');
    const returnBox = $('returnBox');
    const returnDateInput = $('returnDateInput');
    const depInput = $('depDateInput');

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
    $('swapBtn').addEventListener('click', () => {
        const from = $('fromInput');
        const to = $('toInput');
        const fromCity = $('fromCityName');
        const toCity = $('toCityName');

        [from.value, to.value] = [to.value, from.value];
        [fromCity.textContent, toCity.textContent] = [toCity.textContent, fromCity.textContent];

        $('swapBtn').style.transform = 'translateY(-50%) rotate(360deg)';
        setTimeout(() => { $('swapBtn').style.transform = 'translateY(-50%) rotate(0deg)'; }, 400);
    });

    // --- Airport Autocomplete (Custom Elegant Version) ---
    let airportsData = [];
    async function loadAirports() {
        try {
            const res = await fetch('/airports_search.json');
            airportsData = await res.json();
        } catch(e) { console.error("Failed to load airports", e); }
    }
    loadAirports();

    function setupAutocomplete(inputEl, resultsEl, cityDisplayEl) {
        inputEl.addEventListener('input', () => {
            const val = inputEl.value.trim().toLowerCase();
            if (val.length < 1) {
                resultsEl.classList.remove('show');
                return;
            }

            const matches = airportsData.filter(a => 
                (a.code && a.code.toLowerCase().includes(val)) || 
                (a.name && a.name.toLowerCase().includes(val)) || 
                (a.city && a.city.toLowerCase().includes(val))
            ).slice(0, 10);

            if (matches.length > 0) {
                renderAirportResults(matches, resultsEl, inputEl, cityDisplayEl);
                resultsEl.classList.add('show');
            } else {
                resultsEl.classList.remove('show');
            }
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!resultsEl.contains(e.target) && e.target !== inputEl) {
                resultsEl.classList.remove('show');
            }
        });
    }

    function renderAirportResults(matches, container, inputEl, cityDisplayEl) {
        container.innerHTML = '';
        matches.forEach(a => {
            const item = document.createElement('div');
            item.className = 'airport-item';
            item.innerHTML = `
                <div class="airport-icon"><i class="bi bi-airplane"></i></div>
                <div class="airport-info">
                    <div class="airport-main">
                        <span class="airport-name">${a.name}</span>
                        <span class="airport-code">${a.code}</span>
                    </div>
                    <div class="airport-sub">${a.city}, ${a.country}</div>
                </div>
            `;
            item.addEventListener('click', () => {
                inputEl.value = a.code;
                if (cityDisplayEl) cityDisplayEl.textContent = `${a.city}, ${a.country}`;
                container.classList.remove('show');
            });
            container.appendChild(item);
        });
    }

    setupAutocomplete($('fromInput'), $('fromResults'), $('fromCityName'));
    setupAutocomplete($('toInput'), $('toResults'), $('toCityName'));

    // ── Modify panel toggle ──
    $('modifyToggle').addEventListener('click', () => {
        const panel = $('modifyPanel');
        panel.classList.toggle('open');
    });

    $('modifySearchForm')?.addEventListener('submit', function () {
        const btn = $('modifySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Searching…';
    });

    // ── Sort tabs ──
    document.querySelectorAll('.sort-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.sort-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            currentSort = tab.dataset.sort;
            applyFilters();
        });
    });

    // ── Stop chip toggle ──
    document.querySelectorAll('.stop-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const cb = chip.querySelector('input[type=checkbox]');
            cb.checked = !cb.checked;
            chip.classList.toggle('chip-active', cb.checked);
            applyFilters();
        });
    });

    // ── DB-backed search ──
    async function startDbSearch() {
        if (providers.length === 0) {
            loaderIcon.innerHTML = 'Done';
            searchProgressBar.style.width = '100%';
            return;
        }
        
        providers.forEach(p => {
            fetchProviderFromDb(p);
        });
    }

    async function fetchProviderFromDb(provider) {
        try {
            const url = `/api/db-flights/${provider}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
                processNewFlights(data.flights);
                completedProviders.add(provider);
                updateProgress();
            } else {
                console.error(`Failed to fetch ${provider} from DB:`, data.error);
                completedProviders.add(provider);
                updateProgress();
            }
        } catch (e) {
            console.error(`Error fetching ${provider} from DB:`, e);
            completedProviders.add(provider);
            updateProgress();
        }
    }

    // Normalize "10:15 AM" / "10:15" → "10:15" (24h) for consistent matching
    function normTime(t) {
        if (!t) return '';
        t = t.trim();
        const ampm = t.match(/(\d{1,2}):(\d{2})\s*(AM|PM)/i);
        if (ampm) {
            let h = parseInt(ampm[1]);
            const m = ampm[2];
            const p = ampm[3].toUpperCase();
            if (p === 'AM' && h === 12) h = 0;
            if (p === 'PM' && h !== 12) h += 12;
            return `${String(h).padStart(2,'0')}:${m}`;
        }
        return t;
    }

    function processNewFlights(newFlights) {
        if (!newFlights || newFlights.length === 0) return;
        let hasNew = false;
        newFlights.forEach(f => {
            // Key = dep_time + arr_time + stops + round_trip + return_dep_time
            // Does NOT include airline name — provider spellings differ
            const depN = normTime(f.departure_time);
            const arrN = normTime(f.arrival_time);
            const retN = (f.is_round_trip && f.return_leg) ? normTime(f.return_leg.departure_time) : '';
            const flightKey = `${depN}_${arrN}_${f.stops_count}_${f.is_round_trip ? 1 : 0}_${retN}`;
            const existingGroupIndex = allFlights.findIndex(g => g.flightKey === flightKey);
            
            if (existingGroupIndex >= 0) {
                const existingGroup = allFlights[existingGroupIndex];
                const existingOffer = existingGroup.offers.find(o => o.ota_name === f.ota_name && o.price === f.price);
                if (!existingOffer) {
                    existingGroup.offers.push({
                        ota_name: f.ota_name,
                        price: f.price,
                        currency: f.currency,
                        ota_color: f.ota_color,
                        provider: f.provider,
                        search_id: f.search_id,
                        sequence_code: f.sequence_code,
                        fare_id: f.fare_id
                    });
                    existingGroup.offers.sort((a,b) => a.price - b.price);
                    
                    existingGroup.price = existingGroup.offers[0].price;
                    existingGroup.currency = existingGroup.offers[0].currency;
                    existingGroup.ota_name = existingGroup.offers[0].ota_name;
                    existingGroup.ota_color = existingGroup.offers[0].ota_color;
                    existingGroup.provider = existingGroup.offers[0].provider;
                    hasNew = true;
                }
            } else {
                const newGroup = {
                    ...f,
                    flightKey: flightKey,
                    offers: [{
                        ota_name: f.ota_name,
                        price: f.price,
                        currency: f.currency,
                        ota_color: f.ota_color,
                        provider: f.provider,
                        search_id: f.search_id,
                        sequence_code: f.sequence_code,
                        fare_id: f.fare_id
                    }]
                };
                allFlights.push(newGroup);
                hasNew = true;
            }
        });

        if (hasNew) {
            clearSkeletons();
            renderOTAFilters();
            applyFilters();
        }
    }

    function clearSkeletons() {
        flightList.querySelectorAll('.skeleton-card').forEach(s => s.remove());
        noResults.classList.add('d-none');
    }

    function updateProgress() {
        const pct = (completedProviders.size / providers.length) * 100;
        searchProgressBar.style.width = `${pct}%`;
        if (completedProviders.size === providers.length) {
            loaderIcon.classList.add('done');
            loaderIcon.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Done';
            searchProgressBar.style.width = '100%';
            if (allFlights.length === 0) {
                noResults.classList.remove('d-none');
                resultsCountHeader.textContent = 'No flights found';
            }
        }
    }

    function renderOTAFilters() {
        const otaSet = new Set();
        const colors = {};
        allFlights.forEach(g => {
            g.offers.forEach(o => {
                if (o.ota_name) {
                    otaSet.add(o.ota_name);
                    colors[o.ota_name] = o.ota_color;
                }
            });
        });
        const uniqueOTAs = [...otaSet];

        otaFiltersContainer.innerHTML = '';
        uniqueOTAs.forEach(ota => {
            if (!ota) return;
            const slug = String(ota).toLowerCase().replace(/\s+/g, '-');
            const count = allFlights.filter(g => g.offers.some(o => o.ota_name === ota)).length;
            const wrap = document.createElement('div');
            wrap.className = 'ota-item';
            wrap.innerHTML = `
                <input class="filter-trigger ota-check" type="checkbox" id="ota_${slug}" value="${ota}" checked style="display:none;">
                <div class="ota-dot" style="background:${colors[ota]};"></div>
                <span class="ota-name">${ota}</span>
                <span class="ota-count">${count}</span>
            `;
            wrap.addEventListener('click', () => {
                const cb = wrap.querySelector('input');
                cb.checked = !cb.checked;
                wrap.style.opacity = cb.checked ? '1' : '0.4';
                applyFilters();
            });
            otaFiltersContainer.appendChild(wrap);
        });
    }

    function applyFilters() {
        const maxPrice = parseInt(priceRange.value);
        priceValue.textContent = maxPrice.toLocaleString('en-IN');

        const activeOtas = Array.from(document.querySelectorAll('.ota-check'))
            .filter(i => i.checked).map(i => i.value);

        const stopsDirect = $('direct').checked;
        const stopsOnePlus = $('onePlusStop').checked;

        let filtered = allFlights.map(g => {
            const validOffers = activeOtas.length === 0 ? g.offers : g.offers.filter(o => activeOtas.includes(o.ota_name));
            if (validOffers.length === 0) return null;

            return {
                ...g,
                offers: validOffers,
                price: validOffers[0].price,
                currency: validOffers[0].currency,
                ota_name: validOffers[0].ota_name,
                ota_color: validOffers[0].ota_color,
                provider: validOffers[0].provider
            };
        }).filter(g => {
            if (!g) return false;
            const matchesPrice = g.price <= maxPrice;
            let matchesStops = false;
            if (stopsDirect && g.stops_count === 0) matchesStops = true;
            if (stopsOnePlus && g.stops_count >= 1) matchesStops = true;
            return matchesPrice && matchesStops;
        });

        // Sort
        filtered.sort((a, b) => {
            if (currentSort === 'price_asc') return a.price - b.price;
            if (currentSort === 'duration_asc') return a.duration_minutes - b.duration_minutes;
            if (currentSort === 'best_score') return (a.price + a.duration_minutes * 20) - (b.price + b.duration_minutes * 20);
            return 0;
        });

        renderFlights(filtered);
    }

    function renderFlights(flights) {
        flightList.querySelectorAll('.flight-card').forEach(c => c.remove());
        resultsCountHeader.textContent = `${flights.length} flight${flights.length !== 1 ? 's' : ''} found`;

        if (flights.length === 0 && allFlights.length > 0) {
            noResults.classList.remove('d-none');
            return;
        }
        noResults.classList.add('d-none');

        const cardTpl = $('flightCardTemplate');
        const legTpl = $('legTemplate');

        flights.forEach((f, idx) => {
            const clone = cardTpl.content.cloneNode(true);
            const card = clone.querySelector('.flight-card');
            card.style.animationDelay = `${idx * 0.04}s`;

            clone.querySelector('.currency-label').textContent = f.currency + ' ';
            clone.querySelector('.price-value').textContent = Number(f.price).toLocaleString();

            const provTag = clone.querySelector('.provider-label');
            provTag.textContent = f.ota_name;
            provTag.style.background = f.ota_color;

            const btn = clone.querySelector('.select-btn');
            btn.style.background = f.ota_color;

            const handleBookingRedirect = (offer) => {
                if (offer.provider === 'sharetrip' && offer.search_id && offer.sequence_code) {
                    const bookingUrl = `https://sharetrip.net/flight-booking?searchId=${encodeURIComponent(offer.search_id)}&sequenceCode=${encodeURIComponent(offer.sequence_code)}`;
                    window.open(bookingUrl, '_blank');
                } else if (offer.provider === 'gozayaan' && offer.search_id && offer.fare_id) {
                    const listUrl = `https://gozayaan.com/flight/list?search_id=${encodeURIComponent(offer.search_id)}&fare_id=${encodeURIComponent(offer.fare_id)}`;
                    window.open(listUrl, '_blank');
                } else {
                    alert('Booking for ' + offer.ota_name + ' is coming soon!');
                }
            };

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                handleBookingRedirect(f.offers[0]);
            });

            if (f.offers.length > 1) {
                const othersContainer = clone.querySelector('.other-offers-container');
                othersContainer.classList.remove('d-none');
                
                for (let i = 1; i < f.offers.length; i++) {
                    const offer = f.offers[i];
                    const row = document.createElement('div');
                    row.className = 'other-offer-row';
                    row.innerHTML = `
                        <div class="o-off-left">
                            <div class="o-off-dot" style="background: ${offer.ota_color}"></div>
                            <span class="o-off-name">${offer.ota_name}</span>
                        </div>
                        <div class="o-off-price">${offer.currency} ${Number(offer.price).toLocaleString()}</div>
                    `;
                    row.addEventListener('click', () => {
                        handleBookingRedirect(offer);
                    });
                    othersContainer.appendChild(row);
                }
            }

            clone.querySelector('.departure-leg-container').appendChild(buildLeg(f, false, legTpl));

            if (f.is_round_trip && f.return_leg) {
                const retCont = clone.querySelector('.return-leg-container');
                retCont.classList.remove('d-none');
                retCont.appendChild(buildLeg(f.return_leg, true, legTpl));
            }

            flightList.appendChild(clone);
        });
    }

    function buildLeg(legData, isReturn, tpl) {
        const leg = tpl.content.cloneNode(true);
        const airline = legData.airline || 'Unknown';
        const logo = legData.airline_logo;

        const logoWrap = leg.querySelector('.logo-wrap-inner');
        if (logo) {
            const img = document.createElement('img');
            img.src = logo;
            img.alt = airline;
            logoWrap.appendChild(img);
        } else {
            logoWrap.classList.remove('airline-logo-wrap');
            logoWrap.classList.add('airline-initial');
            logoWrap.textContent = airline.charAt(0).toUpperCase();
        }

        leg.querySelector('.airline-name').textContent = airline;
        const badge = leg.querySelector('.leg-badge');
        badge.textContent = isReturn ? 'Return' : 'Depart';
        badge.classList.add(isReturn ? 'return' : 'depart');

        leg.querySelector('.dep-time').textContent = legData.departure_time;
        leg.querySelector('.arr-time').textContent = legData.arrival_time;
        leg.querySelector('.duration-label').textContent = legData.duration;

        const stopsEl = leg.querySelector('.stops-label');
        stopsEl.textContent = legData.stops || 'Non Stop';
        if (!legData.stops || legData.stops === 'Non Stop' || legData.stops === 'Direct') {
            stopsEl.classList.add('direct');
        } else {
            stopsEl.classList.add('has-stops');
        }

        leg.querySelector('.origin-code').textContent = (isReturn ? legData.origin : @json($searchData['from_location'])).substring(0,3).toUpperCase();
        leg.querySelector('.dest-code').textContent   = (isReturn ? legData.destination : @json($searchData['to_location'])).substring(0,3).toUpperCase();

        if (isReturn) {
            const line = leg.querySelector('.flight-line');
            line.style.background = 'linear-gradient(90deg, #10b981, #059669)';
        }

        return leg;
    }

    // ── Listeners ──
    priceRange.addEventListener('input', applyFilters);

    // ── Boot ──
    startDbSearch();
});
</script>

@endsection
