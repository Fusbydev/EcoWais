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
</style>


<div class="sidebar">

    <!-- Logo -->
    <div class="d-flex align-items-center mb-4">
        <span class="me-2 fs-3">🗑️</span>
        <span class="fw-bold fs-4">EcoWais</span>
    </div>

    <!-- Navigation -->
    <nav class="nav flex-column gap-1">

        <a class="nav-link {{ request()->routeIs('municipality.scheduling') ? 'active' : '' }}"
           href="{{ route('municipality.scheduling') }}">
            Barangay Scheduling
        </a>

        <a class="nav-link {{ request()->routeIs('municipality.admin') ? 'active' : '' }}"
           href="{{ route('municipality.admin') }}">
            Admin
        </a>

        <a class="nav-link {{ request()->routeIs('map.view') ? 'active' : '' }}"
           href="{{ route('map.view') }}">
            Map View
        </a>

        <a class="nav-link {{ request()->routeIs('user-management') ? 'active' : '' }}"
           href="{{ route('user-management') }}">
            User Management
        </a>

        <a class="nav-link {{ request()->routeIs('location-manager') ? 'active' : '' }}"
           href="{{ route('location-manager') }}">
            Manage Locations
        </a>

    </nav>

    <!-- User -->
    <div class="user-section">
        <div class="fw-semibold mb-2">{{ session('user_name') }}</div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-danger w-100">Logout</button>
        </form>
    </div>

</div>
