<nav class="navbar">
    <div class="logo">
        <span>🗑️</span> EcoWais
    </div>

    <div class="nav-links">
        <button 
            type="button" 
            id="nav-resident" 
            class="{{ request()->routeIs('barangay.admin.homepage') ? 'active' : '' }}"
            onclick="window.location.href='{{ route('barangay.admin.homepage') }}'">
            Barangay Dashboard
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
