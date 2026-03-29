@extends('layouts.master')

@section('content')

    {{-- ─── Search Summary Header ────────────────────────────────────────── --}}
    <div class="bg-white shadow-sm py-4 mb-4 sticky-top d-print-none" style="top: 0; z-index: 1020;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-subtle p-3 rounded-circle d-none d-md-block">
                            <i class="bi bi-airplane-fill text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bold">
                                {{ $searchData['from_location'] }} 
                                <i class="bi bi-arrow-right-short mx-1 text-primary"></i> 
                                {{ $searchData['to_location'] }}
                            </h4>
                            <p class="text-muted mb-0 small fw-medium">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ \Carbon\Carbon::parse($searchData['departure_date'])->format('D, d M Y') }}
                                @if(!empty($searchData['return_date']))
                                    - {{ \Carbon\Carbon::parse($searchData['return_date'])->format('D, d M Y') }}
                                @endif
                                <span class="mx-2 text-secondary">|</span>
                                <i class="bi bi-people me-1"></i> {{ $searchData['passengers'] }} Traveler(s)
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#modifySearchBox" aria-expanded="false">
                        <i class="bi bi-pencil-square me-2"></i> Modify Search
                    </button>
                </div>
            </div>

            {{-- Collapsible Modify Search Form --}}
            <div class="collapse mt-4" id="modifySearchBox">
                <div class="card card-body border-0 bg-light-subtle shadow-sm rounded-4 p-4 animate-fade-in">
                    <form action="/search" method="POST" id="modifySearchForm">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">From</label>
                                <input type="text" class="form-control" name="from_location" value="{{ $searchData['from_location'] }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">To</label>
                                <input type="text" class="form-control" name="to_location" value="{{ $searchData['to_location'] }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">Depart</label>
                                <input type="date" class="form-control" name="departure_date" value="{{ $searchData['departure_date'] }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-muted">Return</label>
                                <input type="date" class="form-control" name="return_date" value="{{ $searchData['return_date'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small fw-bold text-muted">Adults</label>
                                <input type="number" class="form-control" name="passengers" value="{{ $searchData['passengers'] }}" min="1">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100 fw-bold">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

{{-- ─── Main Layout ─────────────────────────────────────────────────────── --}}
<div class="container mb-5">
    <div class="row">

        {{-- Sidebar Filters --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Filter Results</h5>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Stops</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-trigger" type="checkbox" id="direct" value="0" checked>
                            <label class="form-check-label" for="direct">Direct</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-trigger" type="checkbox" id="onePlusStop" value="1+" checked>
                            <label class="form-check-label" for="onePlusStop">1+ Stop</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">OTA Source</label>
                        <div id="otaFiltersContainer">
                            <div class="text-muted small italic">Waiting for results...</div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold d-flex justify-content-between">
                            <span>Max Price</span>
                            <span id="priceValue" class="text-primary fw-bold">150,000</span>
                        </label>
                        <input type="range" class="form-range" min="1000" max="150000" step="500" id="priceRange" value="150000">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>1,000</span>
                            <span>150,000</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-primary-subtle rounded-4 border border-primary-subtle text-center">
                <i class="bi bi-bell-fill text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold">Price Alerts</h6>
                <p class="small text-muted mb-3">Get notified when prices drop for this route.</p>
                <button class="btn btn-primary btn-sm px-4 fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#priceAlertModal">
                    Set Alert
                </button>
            </div>
        </div>

        {{-- Results Column --}}
        <div class="col-lg-9">

            {{-- Loading Progress & Status --}}
            <div id="loadingStatus" class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold mb-0" id="resultsCountHeader">Searching flights...</h5>
                    <span class="badge bg-light text-dark border p-2 px-3 rounded-pill" id="loaderIcon">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span id="loaderText">Polling Providers</span>
                    </span>
                </div>
                <div class="progress" style="height: 6px; border-radius: 10px;">
                    <div id="searchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            {{-- Sorting Bar --}}
            <div class="d-flex justify-content-end mb-4">
                <select id="sortSwitch" class="form-select w-auto border-0 shadow-sm rounded-pill px-4">
                    <option value="price_asc">Cheapest First</option>
                    <option value="duration_asc">Fastest First</option>
                    <option value="best_score">Best Flights</option>
                </select>
            </div>

            {{-- ─── Flight List ──────────────────────────────────────────────── --}}
            <div id="flightList">
                {{-- Skeletons --}}
                @for($i=0; $i<3; $i++)
                <div class="card skeleton-card mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <div class="skeleton-line w-75 mb-3"></div>
                                <div class="skeleton-line w-50 mb-4"></div>
                                <div class="skeleton-line w-75"></div>
                            </div>
                            <div class="col-4">
                                <div class="skeleton-line w-100 h-100 mb-2" style="height: 40px !important;"></div>
                                <div class="skeleton-line w-100" style="height: 40px !important;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            {{-- No results found view --}}
            <div id="noResults" class="text-center py-5 d-none">
                <img src="https://img.icons8.com/bubbles/200/search.png" alt="No records" class="mb-4">
                <h4 class="fw-bold">No Flights Found</h4>
                <p class="text-muted">Try adjusting your filters or search criteria.</p>
                <button class="btn btn-primary px-4 fw-bold rounded-pill mt-2" onclick="location.reload()">Refresh Search</button>
            </div>

        </div>{{-- /.col-lg-9 --}}
    </div>{{-- /.row --}}
</div>{{-- /.container --}}

{{-- ─── Flight Card Template ────────────────────────────────────────────── --}}
<template id="flightCardTemplate">
    <div class="card flight-card mb-3 shadow-sm border-0 border-start border-4 animate-fade-in" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8 border-end-md">
                    {{-- Departure Leg --}}
                    <div class="departure-leg-container">
                        {{-- Injected via JS --}}
                    </div>

                    {{-- Return Leg --}}
                    <div class="return-leg-container mt-4 pt-3 border-top-dashed d-none">
                        {{-- Injected via JS --}}
                    </div>
                </div>

                {{-- Price & Book (Right Column) --}}
                <div class="col-lg-4 text-center text-lg-end ps-lg-4 mt-4 mt-lg-0 h-100">
                    <div class="price-section d-flex flex-column align-items-lg-end justify-content-center h-100">
                        <div class="text-muted small mb-1">Starting from</div>
                        <div class="price-text mb-0 fs-3 fw-bolder text-dark">
                            <small class="fs-6 fw-normal currency-label"></small> <span class="price-value"></span>
                        </div>
                        <div class="text-muted smaller mb-3 fw-bold provider-label">via Provider</div>
                        <a href="#" class="btn btn-warning fw-bold btn-lg w-100 py-3 shadow-sm select-btn text-white rounded-pill">
                            Select Flight <i class="bi bi-chevron-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- ─── Leg Template ────────────────────────────────────────────────────── --}}
<template id="legTemplate">
    <div class="row align-items-center">
        <div class="col-sm-4 d-flex align-items-center gap-3">
            <img src="" class="airline-logo-img" alt="Airline" style="width: 42px; height: 42px; object-fit: contain;">
            <div class="airline-info">
                <div class="fw-bold text-dark small lh-1 mb-1 airline-name"></div>
                <span class="badge leg-tag fw-bold" style="font-size: 0.6rem;"></span>
            </div>
        </div>
        <div class="col-sm-8 mt-3 mt-sm-0">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <div class="text-start">
                    <div class="fw-bold fs-5 dep-time"></div>
                    <div class="text-muted small fw-bold origin-code"></div>
                </div>
                
                <div class="flex-grow-1 mx-3 text-center position-relative">
                    <div class="text-muted mb-1 duration-label" style="font-size: 0.7rem;"></div>
                    <div class="flight-line">
                        <span class="dot-start"></span>
                        <span class="dot-end"></span>
                    </div>
                    <div class="text-muted mt-1 stops-label" style="font-size: 0.7rem;"></div>
                </div>

                <div class="text-end">
                    <div class="fw-bold fs-5 arr-time"></div>
                    <div class="text-muted small fw-bold dest-code"></div>
                </div>
            </div>
        </div>
    </div>
</template>

{{-- ─── Price Alert Modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="priceAlertModal" tabindex="-1" aria-labelledby="priceAlertModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold" id="priceAlertModalLabel">
            <i class="bi bi-bell-fill text-warning me-2"></i> Create Price Alert
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="/alerts" method="POST">
        @csrf
        <div class="modal-body p-4">
            <p class="text-muted mb-4">
                We'll email you when the price drops for
                <strong>{{ $searchData['from_location'] }}</strong> to
                <strong>{{ $searchData['to_location'] }}</strong>.
            </p>
            <input type="hidden" name="from_location" value="{{ $searchData['from_location'] }}">
            <input type="hidden" name="to_location"   value="{{ $searchData['to_location'] }}">
            <input type="hidden" name="departure_date" value="{{ $searchData['departure_date'] }}">
            <input type="hidden" name="return_date"    value="{{ $searchData['return_date'] ?? '' }}">
            <input type="hidden" name="passengers"     value="{{ $searchData['passengers'] }}">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0 text-dark" id="email"
                           name="email" placeholder="name@example.com" required>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-light border-0 p-4">
            <button type="button" class="btn btn-outline-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm">Save Alert</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    .skeleton-line {
        height: 15px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
        border-radius: 4px;
    }
    @keyframes loading-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .card-body.p-4 { position: relative; }
    .flight-card { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; }
    .flight-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
    .ota-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
    .border-top-dashed { border-top: 1px dashed #dee2e6; }
    .flight-line { height: 2px; background: #0d6efd; position: relative; margin: 8px 0; }
    .dot-start, .dot-end { position: absolute; width: 8px; height: 8px; background: #0d6efd; border-radius: 50%; top: 50%; transform: translateY(-50%); border: 2px solid white; box-shadow: 0 0 0 1px #0d6efd; }
    .dot-start { left: 0; } .dot-end { right: 0; }
    .bg-light-subtle { background-color: #f8f9fa !important; }
    @media (min-width: 992px) { .border-end-md { border-right: 1px solid #f0f0f0; } }
    @keyframes fadeInDown { from { opacity: 0; transform: translate3d(0, -10px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
    .animate-fade-in { animation: fadeInDown 0.4s ease-out; }
    .form-range::-webkit-slider-thumb { background: #0d6efd; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const providers = @json($providers);
    let allFlights = [];
    let completedProviders = new Set();
    let providerState = {}; // { gozayaan: { search_id: null, isCompleted: false } }

    const flightList = id('flightList');
    const loadingStatus = id('loadingStatus');
    const searchProgressBar = id('searchProgressBar');
    const otaFiltersContainer = id('otaFiltersContainer');
    const priceRange = id('priceRange');
    const priceValue = id('priceValue');
    const sortSwitch = id('sortSwitch');
    const noResults = id('noResults');
    const resultsCountHeader = id('resultsCountHeader');
    const loaderText = id('loaderText');

    function id(val) { return document.getElementById(val); }

    async function startSearch() {
        if (providers.length === 0) return;
        providers.forEach(p => {
            providerState[p] = { search_id: null, isCompleted: false };
            fetchProvider(p);
        });
    }

    async function fetchProvider(provider) {
        try {
            let url = `/api/search-flights/${provider}`;
            const state = providerState[provider];
            
            if (state.search_id) {
                url += `?search_id=${encodeURIComponent(state.search_id)}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                // Update state
                providerState[provider].search_id = data.search_id;
                providerState[provider].isCompleted = data.isCompleted;

                // Add to global list if not already present
                processNewFlights(data.flights, provider);

                if (data.isCompleted) {
                    completedProviders.add(provider);
                    checkStatus();
                } else {
                    loaderText.textContent = `Polling flights...`;
                    // Poll again after 3 seconds
                    setTimeout(() => fetchProvider(provider), 3000);
                }
            } else {
                completedProviders.add(provider);
                checkStatus();
            }
        } catch (e) {
            console.error(`Error fetching ${provider}:`, e);
            completedProviders.add(provider);
            checkStatus();
        }
    }

    function processNewFlights(newFlights, provider) {
        if (!newFlights || newFlights.length === 0) return;

        // Deduplication key: Price + Airline + DepTime + OTA
        const existingKeys = new Set(allFlights.map(f => 
            `${f.ota_name}_${f.price}_${f.airline}_${f.departure_time}_${f.is_round_trip}`
        ));

        const freshOnes = newFlights.filter(f => {
            const key = `${f.ota_name}_${f.price}_${f.airline}_${f.departure_time}_${f.is_round_trip}`;
            return !existingKeys.has(key);
        });

        if (freshOnes.length > 0) {
            allFlights = [...allFlights, ...freshOnes];
            updateGlobalUI();
        }
    }

    function checkStatus() {
        const progress = (completedProviders.size / providers.length) * 100;
        searchProgressBar.style.width = `${progress}%`;
        
        if (completedProviders.size === providers.length) {
            id('loaderIcon').classList.remove('bg-light');
            id('loaderIcon').classList.add('bg-success-subtle', 'text-success');
            id('loaderIcon').innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> All Providers Captured';
            searchProgressBar.style.width = '100%';
            searchProgressBar.classList.remove('progress-bar-animated');
        }
    }

    function updateGlobalUI() {
        // Clear skeleton on first result
        if (allFlights.length > 0) {
            const skeletons = flightList.querySelectorAll('.skeleton-card');
            skeletons.forEach(s => s.remove());
            noResults.classList.add('d-none');
        }

        renderOTAFilters();
        applyFilters();
    }

    function renderOTAFilters() {
        const uniqueOTAs = [...new Set(allFlights.map(f => f.ota_name))];
        const colors = {};
        allFlights.forEach(f => colors[f.ota_name] = f.ota_color);

        otaFiltersContainer.innerHTML = '';
        uniqueOTAs.forEach(ota => {
            const slug = ota.toLowerCase().replace(/\s+/g, '-');
            const count = allFlights.filter(f => f.ota_name === ota).length;
            
            const div = document.createElement('div');
            div.className = 'form-check mb-2';
            div.innerHTML = `
                <input class="form-check-input filter-trigger ota-check" type="checkbox" id="ota_${slug}" value="${ota}" checked>
                <label class="form-check-label d-flex align-items-center gap-2 w-100" for="ota_${slug}">
                    <span class="ota-dot" style="background:${colors[ota]};"></span>
                    ${ota}
                    <span class="badge bg-light-subtle text-muted border ms-auto">${count}</span>
                </label>
            `;
            otaFiltersContainer.appendChild(div);
        });

        // Add listeners to new checkboxes
        document.querySelectorAll('.ota-check').forEach(c => c.addEventListener('change', applyFilters));
    }

    function applyFilters() {
        const maxPrice = parseInt(priceRange.value);
        priceValue.textContent = maxPrice.toLocaleString();

        const activeOtas = Array.from(document.querySelectorAll('.ota-check'))
            .filter(i => i.checked)
            .map(i => i.value);

        const stopsDirect = id('direct').checked;
        const stopsOnePlus = id('onePlusStop').checked;

        let filtered = allFlights.filter(f => {
            const matchesPrice = f.price <= maxPrice;
            const matchesOta = activeOtas.includes(f.ota_name);
            let matchesStops = false;
            if (stopsDirect && f.stops_count === 0) matchesStops = true;
            if (stopsOnePlus && f.stops_count >= 1) matchesStops = true;
            return matchesPrice && matchesOta && matchesStops;
        });

        sortFlights(filtered);
        renderFlights(filtered);
    }

    function sortFlights(flights) {
        const criteria = sortSwitch.value;
        flights.sort((a, b) => {
            if (criteria === 'price_asc') return a.price - b.price;
            if (criteria === 'duration_asc') return a.duration_minutes - b.duration_minutes;
            if (criteria === 'best_score') {
                const scoreA = a.price + (a.duration_minutes * 20);
                const scoreB = b.price + (b.duration_minutes * 20);
                return scoreA - scoreB;
            }
            return 0;
        });
    }

    function renderFlights(flights) {
        const template = id('flightCardTemplate');
        const legTemplate = id('legTemplate');
        
        flightList.querySelectorAll('.flight-card').forEach(c => c.remove());
        resultsCountHeader.textContent = `${flights.length} flights found`;

        if (flights.length === 0 && allFlights.length > 0) {
            noResults.classList.remove('d-none');
            return;
        }

        flights.forEach(f => {
            const clone = template.content.cloneNode(true);
            const card = clone.querySelector('.flight-card');
            
            card.style.borderLeftColor = f.ota_color;
            clone.querySelector('.currency-label').textContent = f.currency;
            clone.querySelector('.price-value').textContent = f.price.toLocaleString();
            clone.querySelector('.provider-label').textContent = `via ${f.ota_name}`;
            
            const btn = clone.querySelector('.select-btn');
            btn.style.backgroundColor = f.ota_color;
            btn.style.borderColor = f.ota_color;

            // Render Depart Leg
            const depContainer = clone.querySelector('.departure-leg-container');
            depContainer.appendChild(createLegElement(f, false, legTemplate));

            // Render Return Leg if exists
            if (f.is_round_trip && f.return_leg) {
                const retContainer = clone.querySelector('.return-leg-container');
                retContainer.classList.remove('d-none');
                retContainer.appendChild(createLegElement(f.return_leg, true, legTemplate));
            }

            flightList.appendChild(clone);
        });
    }

    function createLegElement(legData, isReturn, template) {
        const leg = template.content.cloneNode(true);
        const airline = legData.airline;
        const logo = legData.airline_logo;

        if (logo) {
            leg.querySelector('.airline-logo-img').src = logo;
        } else {
            leg.querySelector('.airline-logo-img').remove();
            const placeholder = document.createElement('div');
            placeholder.className = 'airline-logo';
            placeholder.textContent = airline.charAt(0);
            leg.querySelector('.gap-3').prepend(placeholder);
        }

        leg.querySelector('.airline-name').textContent = airline;
        const tag = leg.querySelector('.leg-tag');
        tag.textContent = isReturn ? 'RETURN' : 'DEPART';
        tag.classList.add(isReturn ? 'bg-success-subtle' : 'bg-primary-subtle');
        tag.classList.add(isReturn ? 'text-success' : 'text-primary');
        tag.classList.add(isReturn ? 'border-success-subtle' : 'border-primary-subtle', 'border', 'rounded-pill');

        leg.querySelector('.dep-time').textContent = legData.departure_time;
        leg.querySelector('.arr-time').textContent = legData.arrival_time;
        leg.querySelector('.duration-label').textContent = legData.duration;
        leg.querySelector('.stops-label').textContent = legData.stops;
        
        leg.querySelector('.origin-code').textContent = (isReturn ? legData.origin : @json($searchData['from_location'])).substring(0,3).toUpperCase();
        leg.querySelector('.dest-code').textContent = (isReturn ? legData.destination : @json($searchData['to_location'])).substring(0,3).toUpperCase();

        if (isReturn) {
            leg.querySelector('.flight-line').classList.add('bg-success-subtle');
            leg.querySelector('.dot-start').classList.add('bg-success');
            leg.querySelector('.dot-end').classList.add('bg-success');
        }

        return leg;
    }

    // Filter listeners
    priceRange.addEventListener('input', applyFilters);
    sortSwitch.addEventListener('change', applyFilters);
    document.querySelectorAll('.filter-trigger').forEach(el => el.addEventListener('change', applyFilters));

    // Start
    startSearch();

    // Modify Search loading state
    document.getElementById('modifySearchForm')?.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    });
});
</script>
@endsection
