<nav class="navbar">
    @if(session()->has('user_id'))
        <ul>
            {{-- Barangay Admin Navbar --}}
            @if(session('user_role') === 'barangay_admin')
                <li><a href="{{ route('barangay.admin.homepage') }}">Dashboard</a></li>
                <li><a href="{{ route('barangay.admin.drivers') }}">Drivers</a></li>
                <li><a href="{{ route('barangay.admin.reports') }}">Reports</a></li>

            {{-- Municipality Admin Navbar --}}
            @elseif(session('user_role') === 'municipality_administrator')
                <li><a href="{{ route('municipality.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('municipality.barangays') }}">Barangays</a></li>
                <li><a href="{{ route('municipality.reports') }}">Reports</a></li>

            {{-- Barangay Waste Collector Navbar --}}
            @elseif(session('user_role') === 'barangay_waste_collector')
                <li><a href="{{ route('collector.dashboard') }}">My Routes</a></li>
                <li><a href="{{ route('collector.schedule') }}">Schedule</a></li>

            {{-- Default (just in case) --}}
            @else
                <li><a href="{{ route('homepage') }}">Home</a></li>
            @endif
        </ul>

        <div class="navbar-right">
            <span>Welcome, {{ session('user_name') ?? 'User' }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-link">Logout</button>
            </form>
        </div>
    @else
        {{-- Guest Navbar --}}
        <ul>
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register</a></li>
        </ul>
    @endif
</nav>
