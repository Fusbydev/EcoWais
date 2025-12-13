<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #0d253f;
        color: #fff;
        padding: 1.5rem 1rem;
        display: flex;
        flex-direction: column;
        z-index: 1050;
        transition: transform 0.3s ease-in-out;
    }

    .sidebar .nav-link {
        color: #cbd5e1;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: 0.2s;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
        color: #fff;
    }

    .sidebar .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
    }

    .sidebar .user-section {
        margin-top: auto;
        border-top: 1px solid rgba(255,255,255,0.2);
        padding-top: 1.2rem;
    }

    /* Mobile Styles */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

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
    }

    /* Toggle Button */
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
        display: none;
    }

    @media (max-width: 991.98px) {
        .sidebar-toggle {
            display: block;
        }
    }

    /* Adjust main content when sidebar is visible on desktop */
    @media (min-width: 992px) {
        .main-content {
            margin-left: 250px;
        }
    }
</style>

<!-- Toggle Button (visible on mobile only) -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list fs-4"></i>
</button>

<!-- Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="d-flex align-items-center mb-4">
    <img src="/assets/logo (1).png" alt="EcoWais Logo"
         style="width: 200px; height: 80px;" class="me-2">
</div>

    <!-- Navigation -->
    <nav class="nav flex-column gap-1">
        <a class="nav-link {{ request()->routeIs('barangay.admin.homepage') ? 'active' : '' }}"
           href="{{ route('barangay.admin.homepage') }}">
           <i class="bi bi-speedometer2 me-2"></i>Barangay Dashboard
        </a>

        <a class="nav-link {{ request()->routeIs('barangay.admin.report') ? 'active' : '' }}"
           href="{{ route('barangay.admin.report') }}">
           <i class="bi bi-flag-fill"></i>  Report an Issue
        </a>

        <a class="nav-link {{ request()->routeIs('barangay.admin.attendance') ? 'active' : '' }}"
           href="{{ route('barangay.admin.attendance') }}">
           <i class="bi bi-list-check"></i>  Attendance
        </a>
    </nav>

    <!-- User Section -->
    <div class="user-section">
        <div class="fw-semibold">{{ session('user_name') }}</div>

        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button class="btn btn-sm btn-danger w-100">Logout</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

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
        const navLinks = sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });
        });
    });
</script>