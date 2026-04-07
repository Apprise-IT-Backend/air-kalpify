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
    --border: #e8edf3;
    --text-1: #0f172a;
    --text-2: #475569;
    --text-3: #94a3b8;
    --radius: 16px;
    --shadow-sm: 0 2px 12px rgba(0,0,0,0.06);
    --shadow-md: 0 6px 30px rgba(0,0,0,0.10);
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
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 20px 24px;
    display: none;
}
.modify-search-panel.open { display: block; }
.modify-search-panel .form-control {
    border-radius: 10px;
    border: 1.5px solid var(--border);
    padding: 10px 14px;
    font-size: 0.88rem;
    font-weight: 500;
}
.modify-search-panel .form-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-3);
    margin-bottom: 6px;
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
        <div class="container-xl px-0">
            <form action="/search" method="POST" id="modifySearchForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="text" class="form-control" name="from_location" value="{{ $searchData['from_location'] }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="text" class="form-control" name="to_location" value="{{ $searchData['to_location'] }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Departure</label>
                        <input type="date" class="form-control" name="departure_date" value="{{ $searchData['departure_date'] }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Return</label>
                        <input type="date" class="form-control" name="return_date" value="{{ $searchData['return_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-4">
                        <label class="form-label">Adults / Children</label>
                        <div class="d-flex gap-2">
                            <input type="number" class="form-control px-2" name="adults" value="{{ $searchData['adults'] }}" min="1" title="Adults">
                            <input type="number" class="form-control px-2" name="children" value="{{ $searchData['children'] }}" min="0" title="Children (5-11)">
                        </div>
                    </div>
                    <div class="col-md-2 col-4">
                        <label class="form-label">Kids / Infants</label>
                        <div class="d-flex gap-2">
                            <input type="number" class="form-control px-2" name="kids" value="{{ $searchData['kids'] ?? 0 }}" min="0" title="Kids (2-4)">
                            <input type="number" class="form-control px-2" name="infants" value="{{ $searchData['infants'] }}" min="0" title="Infants">
                        </div>
                    </div>
                    <div class="col-md-2 col-4">
                        <label class="form-label">Cabin</label>
                        <select class="form-select form-control px-2" name="cabin_class" style="cursor: pointer;">
                            <option value="Economy" {{ ($searchData['cabin_class'] ?? 'Economy') == 'Economy' ? 'selected' : '' }}>Economy</option>
                            <option value="Business" {{ ($searchData['cabin_class'] ?? '') == 'Business' ? 'selected' : '' }}>Business</option>
                            <option value="First" {{ ($searchData['cabin_class'] ?? '') == 'First' ? 'selected' : '' }}>First</option>
                        </select>
                    </div>
                    <input type="hidden" name="trip_type" value="{{ $searchData['trip_type'] }}">
                    <div class="col-md-12 mt-3 text-end d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 py-2" id="modifySubmitBtn">
                            <i class="bi bi-search me-2"></i>Search Again
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
