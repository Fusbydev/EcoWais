@extends ('layouts.app')

@section ('content')
    <div id="admin-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Admin Dashboard</h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="total-trucks">{{ $trucks->count() }}</div>
                    <div>Total Trucks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="active-drivers">{{ $drivers->count() }}</div>
                    <div>Active Collectors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="registered-residents">{{ $locations->count() }}</div>
                    <div>Registered Barangays</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="system-efficiency">89%</div>
                    <div>System Efficiency</div>
                </div>
            </div>


<div class="card">
    <div class="container mt-5">

    <!-- DASHBOARD CARDS -->
    <div class="row g-4 mb-4">
        <!-- Waste Collected Today -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-recycle text-success fs-1 me-3"></i>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Waste Collected Today</h6>
                        <h2 class="fw-bold text-success mb-1">{{ $todayTotal ?? 0 }} kg</h2>
                        <small class="text-muted">As of {{ now()->format('F d, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Waste Collected This Month -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-calendar-month text-primary fs-1 me-3"></i>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Waste Collected This Month</h6>
                        <h2 class="fw-bold text-primary mb-1">{{ $monthTotal ?? 0 }} kg</h2>
                        <small class="text-muted">Month of {{ now()->format('F Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Collection Entries -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-clipboard-data text-warning fs-1 me-3"></i>
                    <div>
                        <h6 class="card-subtitle mb-2 text-muted">Total Collections</h6>
                        <h2 class="fw-bold text-warning mb-1">{{ $totalCollections ?? 0 }}</h2>
                        <small class="text-muted">All-time entries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAPHS SECTION -->
    <div class="row g-4">
        <!-- Daily Waste Chart -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bi bi-bar-chart-fill fs-5 me-2"></i>
                    <span>Daily Waste Collection (kg)</span>
                </div>
                <div class="card-body">
                    <canvas id="dailyWasteChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Waste by Type -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-success text-white d-flex align-items-center">
                    <i class="bi bi-pie-chart-fill fs-5 me-2"></i>
                    <span>Waste Collected by Type</span>
                </div>
                <div class="card-body">
                    <canvas id="wasteTypeChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

</div>

            <div class="card">
                <h3>Fleet Management</h3>

                @if(session('truckSuccess'))
                    <div class="alert alert-success">
                        {{ session('truckSuccess') }}
                    </div>
                @endif

                <div class="search-filter">
                    <input type="text" id="fleet-search" placeholder="Search trucks or drivers...">
                    <select id="fleet-status-filter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTruckModal">
                        Add New Truck
                    </button>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Truck ID</th>
                            <th>Driver</th>
                            <th>Current Location</th>
                            <th>Status</th>
                            <th>Fuel Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fleet-table">
                        @foreach($trucks as $truck)
                            <tr>
                                <td>{{ $truck->truck_id }}</td>
                                <td>{{ $truck->driver->user->name ?? 'N/A' }}</td>
                                <td>{{ $truck->initial_location }}</td>
                                <td>{{ ucfirst($truck->status) }}</td>
                                <td>{{ $truck->initial_fuel }}%</td>
                                <td>
                                    <button class="btn btn-sm btn-primary">Edit</button>
                                    <button class="btn btn-sm btn-warning">View</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <div class="card">
                <h3>Pending Reports</h3>
                <div class="search-filter">
                    <select id="report-filter">
                        <option value="">All Reports</option>
                        <option value="new">New</option>
                        <option value="in-review">In Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <select id="report-type-filter">
                        <option value="">All Types</option>
                        <option value="missed">Missed Collection</option>
                        <option value="spillage">Spillage</option>
                        <option value="vehicle">Vehicle Issues</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <!--<th>Reporter</th>-->
                            <th>Type</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="admin-reports">
    @foreach($reports as $report)
        <tr>
            <td>{{ $report->created_at ? $report->created_at->format('M d, Y h:i A') : '' }}</td>
            <!--<td>{{ $report->reporter->name ?? 'N/A' }}</td>-->
            <td>
                @php
                    if ($report->issue_type === 'other') {
                        $issueDisplay = $report->other_issue ?? '';
                    } elseif ($report->issue_type === 'driver-absent') {
                        $driverName = $report->driver->user->name ?? 'Unknown';
                        $issueDisplay = "Absent - " . $driverName;
                    } else {
                        $issueDisplay = $report->issue_type ?? '';
                    }
                @endphp
                {{ $issueDisplay }}
            </td>
            <td>{{ $report->location ?? '' }}</td>
            <td>{{ ucfirst($report->priority ?? '') }}</td>
            <td><span class="badge bg-info">{{ $report->status ?? 'Pending' }}</span></td>
            <td>
                <!-- reesolved and view button -->
                <button class="btn btn-sm btn-primary">Resolve</button>
                <button class="btn btn-sm btn-warning">View</button>
            </td>
        </tr>
    @endforeach
</tbody>


                </table>
            </div>
            <!-- Success alert -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        <div class="alert alert-info">
            Auto-generated login credentials:<br>
            Email: <strong>{{ session('generated_email') }}</strong><br>
            Password: <strong>{{ session('generated_password') }}</strong>
        </div>
    @endif                            
            <div class="card">
                <h3>System Settings</h3>
                <div class="grid-auto">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageLocationsModal">
                        Manage Locations
                    </button>


                    <a href="{{ route('reports.generate.pdf') }}" class="btn btn-primary">
                        Generate PDF Report
                    </a>
                    <!--<button class="btn" onclick="backupData()">Backup Data</button>-->
                    <a href="{{ route('user-management') }}" class="btn btn-primary">User Management</a></a>
                </div>
            </div>


            <div class="modal fade" id="addTruckModal" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTruckModalLabel">➕ Add New Truck</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{  route('trucks.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                        <div class="mb-3">
                            <label for="truck_id" class="form-label">Truck ID</label>
                            <input type="text" class="form-control" id="truck_id" name="truck_id" required>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted">Note: If the driver is already assigned to a truck, they will be disabled.</p>
                            <label for="driver_id" class="form-label">Driver Name</label>
                            <select name="driver_id" class="form-select">
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                @php
                                    $assignedTruck = $driver->truck; // Truck model if assigned
                                    $isDeactivated = $driver->user->status === 'deactivated';
                                @endphp
                                <option value="{{ $driver->id }}" {{ $assignedTruck || $isDeactivated ? 'disabled' : '' }}>
                                    {{ $driver->user->name ?? 'Unknown' }}
                                    @if($assignedTruck)
                                        (Truck ID: {{ $assignedTruck->truck_id }})
                                    @elseif($isDeactivated)
                                        (Deactivated)
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        </div>
                        <div class="mb-3">
                            <label for="initial_location" class="form-label">Initial Location</label>
                            <select class="form-control" id="initial_location" name="initial_location" required>
                                <option value="">-- Select Location --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->location }}">{{ $location->location }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="initial_fuel" class="form-label">Initial Fuel (%)</label>
                            <input type="number" class="form-control" id="initial_fuel" name="initial_fuel" min="0" max="100" value="100" required>
                        </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Add Truck</button>
                        </div>
                    </form>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <!-- Location management modal -->
<div class="modal fade" id="manageLocationsModal" tabindex="-1" aria-labelledby="manageLocationsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="manageLocationsLabel">Add Barangay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('locations.store') }}">
                @csrf <!-- <-- CSRF token added -->
                <div class="modal-body">

                    <p class="text-muted">Pin the location (barangay) on the map</p>

                    <!-- Map Area -->
                    <div class="mb-3">
                        <label class="form-label">Pin Location on Map</label>
                        <div id="map" style="height: 350px; width: 100%; border-radius: 8px;"></div>
                    </div>

                    <!-- Barangay Name -->
                    <div class="mb-3">
                        <label for="location" class="form-label">Barangay Name</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>

                    <!-- Admin Dropdown -->
                    <div class="mb-3">
                        <label for="adminId" class="form-label">Assigned Admin</label>
                        <select class="form-select" id="adminId" name="adminId" required>
                            <option value="" selected>-- Select Admin --</option>
                            @foreach($users as $user)
                            @if($user->role === 'barangay_admin')
                                @php
                                    $isAssigned = \App\Models\Location::where('adminId', $user->id)->exists();
                                @endphp

                                @if(!$isAssigned)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endif
                            @endif
                        @endforeach

                        </select>
                    </div>

                    <!-- Latitude -->
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="latitude" name="latitude" readonly required>
                    </div>

                    <!-- Longitude -->
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="longitude" name="longitude" readonly required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </form>

        </div>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
const dailyCtx = document.getElementById('dailyWasteChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($dailyLabels) !!},
            datasets: [{
                label: 'Waste Collected (kg)',
                data: {!! json_encode($dailyData) !!},
                backgroundColor: '#4dabf7'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Kg' }, beginAtZero: true }
            }
        }
    });

    // Waste by Type Chart
    const typeCtx = document.getElementById('wasteTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($typeData)) !!},
            datasets: [{
                label: 'Waste by Type (kg)',
                data: {!! json_encode(array_values($typeData)) !!},
                backgroundColor: ['#f06595','#51cf66','#fcc419','#339af0']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

 document.addEventListener('DOMContentLoaded', function () {
    let map;
    let marker;

    const modalEl = document.getElementById('manageLocationsModal');

    modalEl.addEventListener('shown.bs.modal', function () {
        // Initialize map only once
        if (!map) {
            map = L.map('map').setView([13.411, 121.180], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Click to place marker
            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng, { draggable: true }).addTo(map);

                // Update lat/lng inputs
                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);

                // Reverse geocode to fill Barangay name
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.address) {
                            const barangay = data.address.suburb || data.address.village || data.address.hamlet || '';
                            if (barangay) {
                                document.getElementById('location').value = barangay;
                            }
                        }
                    })
                    .catch(err => console.warn('Reverse geocoding failed', err));

                // Update lat/lng if marker is dragged
                marker.on('dragend', function(ev) {
                    const p = ev.target.getLatLng();
                    document.getElementById('latitude').value = p.lat.toFixed(6);
                    document.getElementById('longitude').value = p.lng.toFixed(6);

                    // Update Barangay name on drag
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${p.lat}&lon=${p.lng}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.address) {
                                const barangay = data.address.suburb || data.address.village || data.address.hamlet || '';
                                if (barangay) {
                                    document.getElementById('location').value = barangay;
                                }
                            }
                        })
                        .catch(err => console.warn('Reverse geocoding failed', err));
                });
            });
        }

        // Fix map sizing after modal opens
        setTimeout(function() {
            map.invalidateSize();
        }, 200);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('fleet-search');
    const statusFilter = document.getElementById('fleet-status-filter');
    const table = document.getElementById('fleet-table');
    const rows = Array.from(table.getElementsByTagName('tr'));

    function filterFleet() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const truckId = row.cells[0].innerText.toLowerCase();
            const driverName = row.cells[1].innerText.toLowerCase();
            const status = row.cells[3].innerText.toLowerCase();

            const matchesSearch = truckId.includes(searchTerm) || driverName.includes(searchTerm);
            const matchesStatus = !statusTerm || status === statusTerm;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterFleet);
    statusFilter.addEventListener('change', filterFleet);
});
</script>

@endsection     