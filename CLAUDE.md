# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

AirTicket — a flight comparison MVP that aggregates prices from multiple OTA segments (GoZayaan, ShareTrip, Flightexpert, Airtickets). Currently uses mock/simulated flight data, not live OTA APIs.

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2+), Eloquent ORM, SQLite
- **Frontend**: Blade templates, Bootstrap 5 + Tailwind CSS v4, vanilla JS + Axios
- **Build**: Vite 8 with `laravel-vite-plugin` and `@tailwindcss/vite`
- **Testing**: Pest 4 (PHPUnit-based), SQLite in-memory for tests
- **Scraping tools**: Guzzle HTTP + Symfony DomCrawler (included but not yet wired to real OTAs)

## Commands

```bash
composer setup          # Full setup: install deps, env, key:generate, migrate, npm install, build
composer dev            # Run 4 concurrent processes: artisan serve, queue:listen, pail (logs), vite dev
composer test           # Run Pest test suite
php artisan serve       # Laravel dev server on port 8000
npm run dev             # Vite dev server with HMR
npm run build           # Production build of CSS/JS
php artisan migrate     # Run database migrations
php artisan pint        # Code formatting (Laravel Pint)
```

## Architecture

Server-side MVC monolith with session-based state. No SPA, no API layer — Blade renders everything server-side.

**Routes** (defined in `routes/web.php`):
- `GET /` → `HomeController@index` — search form with airport autocomplete
- `POST /search` → `FlightController@search` — validates input, stores in session, redirects
- `GET /results` → `FlightController@results` — reads session, generates mock flights, renders
- `POST /alerts` → `AlertController@store` — creates a `FlightAlert` record

**Flight search flow**: Form POST → session storage → redirect to results → mock flight generation using deterministic price hashing by route. Prices vary by airline and stop count.

**Price alerts**: `FlightAlert` model stores email + route + dates. Background command `php artisan check:flight-prices` simulates price monitoring (not connected to real data yet).

**Airport autocomplete**: `process_airports.cjs` (Node script) preprocesses `public/airport-codes.csv` into `public/airports_search.json`, loaded client-side into an HTML datalist.

## Key Conventions

- Dual CSS frameworks: Bootstrap 5 (via CDN in master layout) for structure, Tailwind v4 (via Vite) for utilities. Both are intentional.
- Tailwind is configured purely in `resources/css/app.css` (no `tailwind.config.js`), using v4's CSS-based config with `@import 'tailwindcss'`.
- Custom font: Instrument Sans (loaded from Google Fonts in master layout).
- Master layout: `resources/views/layouts/master.blade.php`.
