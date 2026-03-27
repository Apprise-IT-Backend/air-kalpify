@extends('layouts.master')

@section('content')
<div class="bg-primary text-white py-4 mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">
                {{ $searchData['from_location'] }} <i class="bi bi-arrow-right mx-2"></i> {{ $searchData['to_location']
                }}
            </h4>
            <p class="mb-0 mt-1 opacity-75">
                Depart: {{ \Carbon\Carbon::parse($searchData['departure_date'])->format('D, M j, Y') }}
                @if($searchData['return_date'])
                | Return: {{ \Carbon\Carbon::parse($searchData['return_date'])->format('D, M j, Y') }}
                @endif
                | {{ $searchData['passengers'] }} Passenger(s)
            </p>
        </div>
        <button class="btn btn-light fw-bold px-4" data-bs-toggle="modal" data-bs-target="#priceAlertModal">
            <i class="bi bi-bell-fill text-warning me-2"></i> Track Price
        </button>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Sidebar filters -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Filter Results</h5>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Stops</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="direct">
                            <label class="form-check-label" for="direct">Direct</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="oneStop">
                            <label class="form-check-label" for="oneStop">1 Stop</label>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Price Range</label>
                        <input type="range" class="form-range" min="100" max="1000" id="priceRange">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>$100</span>
                            <span>$1000</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flight Results -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">{{ count($flights) }} flights found</h5>
                <select class="form-select w-auto border-0 shadow-sm">
                    <option>Cheapest First</option>
                    <option>Fastest First</option>
                    <option>Best Flights</option>
                </select>
            </div>

            @foreach($flights as $flight)
            <div class="card flight-card p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-start mb-3 mb-md-0 d-flex align-items-center gap-3">
                        <div class="airline-logo">{{ substr($flight['airline'], 0, 1) }}</div>
                        <span class="fw-bold text-muted">{{ $flight['airline'] }}</span>
                    </div>
                    <div class="col-md-6 text-center mb-3 mb-md-0">
                        <div class="d-flex justify-content-center align-items-center gap-4">
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $flight['departure_time'] }}</h4>
                                <small class="text-muted">{{ strtoupper(substr($searchData['from_location'], 0, 3))
                                    }}</small>
                            </div>
                            <div class="text-center px-3" style="flex-grow: 1; max-width: 200px;">
                                <small class="text-muted d-block mb-1">{{ $flight['stops'] ?? 'Direct' }}</small>
                                <div class="border-bottom border-2 border-primary position-relative">
                                    <i
                                        class="bi bi-airplane-fill text-primary position-absolute top-50 start-50 translate-middle"></i>
                                </div>
                                <small class="text-muted d-block mt-1">{{ $flight['duration'] ?? 'Unknown' }}</small>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $flight['arrival_time'] }}</h4>
                                <small class="text-muted">{{ strtoupper(substr($searchData['to_location'], 0, 3))
                                    }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center text-md-end border-start-md border-light">
                        <div class="price-text mb-2">${{ number_format($flight['price'], 2) }}</div>
                        <button class="btn btn-primary fw-bold w-100 py-2">Select <i
                                class="bi bi-chevron-right ms-1"></i></button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Price Alert Modal -->
<div class="modal fade" id="priceAlertModal" tabindex="-1" aria-labelledby="priceAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold" id="priceAlertModalLabel"><i
                        class="bi bi-bell-fill text-warning me-2"></i> Create Price Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="/alerts" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted mb-4">We'll email you when the price drops for <strong>{{
                            $searchData['from_location'] }}</strong> to <strong>{{ $searchData['to_location']
                            }}</strong>.</p>

                    <input type="hidden" name="from_location" value="{{ $searchData['from_location'] }}">
                    <input type="hidden" name="to_location" value="{{ $searchData['to_location'] }}">
                    <input type="hidden" name="departure_date" value="{{ $searchData['departure_date'] }}">
                    <input type="hidden" name="return_date" value="{{ $searchData['return_date'] ?? '' }}">
                    <input type="hidden" name="passengers" value="{{ $searchData['passengers'] }}">

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email"
                                placeholder="name@example.com" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Alert</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection