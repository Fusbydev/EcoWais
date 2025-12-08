<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoWais</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    @stack('styles')

    <style>
        .sidebar {
            width: 240px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            transition: transform 0.3s ease-in-out;
        }

        main {
            transition: margin-left 0.3s ease-in-out;
        }

        /* Desktop: sidebar visible, main content shifted */
        @media (min-width: 992px) {
            main {
                margin-left: 240px;
                padding: 20px;
            }
        }

        /* Mobile/Tablet: no margin, centered content */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            main {
                margin-left: 0;
                padding: 20px;
                width: 100%;
            }

            /* Add top padding on mobile to avoid overlap with toggle button */
            main {
                padding-top: 70px;
            }
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Toggle button */
        .sidebar-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1030;
            background-color: #0d253f;
            color: #fff;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            display: none;
        }

        @media (max-width: 991.98px) {
            .sidebar-toggle {
                display: block;
            }
        }

        .sidebar-toggle:hover {
            background-color: #1a3a5c;
        }
    </style>
</head>
<body>
    <!-- Toggle Button (visible on mobile only) -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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

    <!-- ✅ Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle && sidebar && sidebarOverlay) {
                // Toggle sidebar on button click
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });

                // Close sidebar when clicking overlay
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });

                // Close sidebar when clicking a link on mobile
                const navLinks = sidebar.querySelectorAll('.nav-link, a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 992) {
                            sidebar.classList.remove('show');
                            sidebarOverlay.classList.remove('show');
                        }
                    });
                });
            }
        });
    </script>

    <!-- ✅ Your app script LAST -->
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('js/assignRoutes.js') }}"></script>
    @stack('scripts')
</body>
</html>