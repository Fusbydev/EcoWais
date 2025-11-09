<nav class="navbar">
    <div class="logo">
        <span>🗑️</span> EcoWais
    </div>

    <div class="nav-links">
        <button 
            id="nav-dashboard" 
            class="{{ request()->routeIs('municipality.dashboard') ? 'active' : '' }}"
            onclick="window.location.href='{{  route('municipality.dashboard') }}'">
            Dashboard
        </button>

        <button 
            id="nav-admin-barangay" 
            class="{{ request()->routeIs('municipality.scheduling') ? 'active' : '' }}"
            onclick="window.location.href='{{  route('municipality.scheduling') }}'">
            Barangay Scheduling
        </button>

        <button 
            id="nav-driver" 
            class="{{ request()->routeIs('barangay.waste.collector.homepage') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('barangay.waste.collector.homepage') }}'">
            Drivers
        </button>

        <button 
            id="nav-admin" 
            class="{{ request()->routeIs('municipality.admin') ? 'active' : '' }}"
            onclick="window.location.href='{{  route('municipality.admin') }}'">
            Admin
        </button>

        <button 
            id="nav-tracking" 
            class="{{ request()->routeIs('map.view') ? 'active' : '' }}"
            onclick="window.location.href='{{  route('map.view') }}'">
            Map View
        </button>

        <div class="user-info">
            <span>{{ session('user_name') }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" id="logoutBtn">Logout</button>
            </form>
        </div>
    </div>
</nav>
