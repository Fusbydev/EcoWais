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

    {{-- ✅ Mapillary v4 CSS (UPDATED) --}}
    <link rel="stylesheet" href="https://unpkg.com/mapillary-js@4.1.2/dist/mapillary.css" />

    {{-- MarkerCluster CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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

<!-- ✅ Load Leaflet FIRST -->
<!-- ✅ Load Leaflet FIRST -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- ✅ Then MarkerCluster -->
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<!-- ✅ osmtogeojson from jsdelivr -->
<script src="https://cdn.jsdelivr.net/npm/osmtogeojson@3.0.0-beta.5/osmtogeojson.js"></script>

<!-- ✅ TEST: Verify it loaded -->
<script>
    console.log('osmtogeojson loaded?', typeof osmtogeojson !== 'undefined');
</script>

<!-- ✅ Mapillary v4 -->
<script src="https://unpkg.com/mapillary-js@4.1.2/dist/mapillary.js"></script>

<!-- ✅ Your app script LAST -->
<script src="{{ asset('js/index.js') }}"></script>
    @stack('scripts')
</body>
</html>