@extends('layouts.master')

@section('content')
<div class="hero">
    <div class="container pb-5">
        <h1 class="display-4 fw-bold mb-3">Find Your Next Adventure</h1>
        <p class="lead mb-4">Compare and book flights with ease.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="search-card">
                <form action="/search" method="POST">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 position-relative">
                            <label for="from_location" class="form-label fw-semibold">From</label>
                            <input type="text" class="form-control form-control-lg" id="from_location" name="from_location" placeholder="City or Airport" value="{{ old('from_location') }}" required autocomplete="off" list="airportsList">
                        </div>
                        <div class="col-md-3 position-relative">
                            <label for="to_location" class="form-label fw-semibold">To</label>
                            <input type="text" class="form-control form-control-lg" id="to_location" name="to_location" placeholder="City or Airport" value="{{ old('to_location') }}" required autocomplete="off" list="airportsList">
                        </div>
                        
                        <datalist id="airportsList"></datalist>

                        <div class="col-md-2">
                            <label for="departure_date" class="form-label fw-semibold">Depart</label>
                            <input type="date" class="form-control form-control-lg" id="departure_date" name="departure_date" value="{{ old('departure_date') ?: date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label for="return_date" class="form-label fw-semibold">Return <small class="text-muted fw-normal">(Optional)</small></label>
                            <input type="date" class="form-control form-control-lg" id="return_date" name="return_date" value="{{ old('return_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="passengers" class="form-label fw-semibold">Passengers</label>
                            <select class="form-select form-select-lg" id="passengers" name="passengers" required>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('passengers') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">Search Flights <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="mt-5 text-center">
                <h3 class="fw-bold mb-4">Popular Destinations</h3>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="https://images.unsplash.com/photo-1499856871958-5b9627545d1a?auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Paris" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Paris</h5>
                                <p class="card-text text-muted">Flights from $299</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Tokyo" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Tokyo</h5>
                                <p class="card-text text-muted">Flights from $599</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Dubai" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">Dubai</h5>
                                <p class="card-text text-muted">Flights from $399</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('/airports_search.json');
        const airports = await response.json();
        
        const datalist = document.getElementById('airportsList');
        
        // Populate the datalist
        airports.forEach(airport => {
            if (airport.code) {
                const option = document.createElement('option');
                option.value = airport.code;
                option.textContent = `${airport.name} (${airport.city}, ${airport.country})`;
                datalist.appendChild(option);
            }
        });
    } catch (e) {
        console.error('Failed to load airports', e);
    }
});
</script>
@endsection
