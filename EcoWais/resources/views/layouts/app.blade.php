<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoWais</title>

    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    {{-- MarkerCluster CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS (for modal functionality) -->


    @stack('styles')
</head>
<body>
    {{-- Navbar based on role --}}
    @if(session('user_role') === 'municipality_administrator')
        @include('layouts.nav_municipality')
    @elseif(session('user_role') === 'barangay_admin')
        @include('layouts.nav_barangay')
    @elseif(session('user_role') === 'barangay_waste_collector')
        @include('layouts.nav_collector')
    @endif

    <main>
        @yield('content')
    </main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- ✅ Load scripts in correct order --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    {{-- Your external JS --}}
    <script src="{{ asset('js/index.js') }}"></script>

    @stack('scripts')
</body>
</html>
