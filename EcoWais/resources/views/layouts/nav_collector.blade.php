<nav class="navbar">
    <div class="logo">
        <span>🗑️</span> EcoWais
    </div>

    <div class="nav-links">
        <button 
            type="button" 
            id="nav-admin-barangay"
            class="{{ request()->routeIs('barangay.scheduling') ? 'active' : '' }}"
            onclick="window.location.href=''">
            Barangay Scheduling
        </button>

        <button 
            type="button" 
            id="nav-driver"
            class="{{ request()->routeIs('barangay.waste.collector.homepage') ? 'active' : '' }}"
            onclick="window.location.href='{{  route('barangay.waste.collector.homepage') }}'">
            Driver
        </button>

        <button 
            type="button" 
            id="nav-tracking"
            class="{{ request()->routeIs('map.view') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('map.view') }}'">
            Map View
        </button>

        <div class="user-info">
            <span>{{ session('user_name') }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
</nav>
