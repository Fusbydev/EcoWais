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
                            <th>Reporter</th>
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
            <td>{{ $report->reporter->name ?? 'N/A' }}</td>
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
                    <button class="btn" onclick="manageRoutes()">Manage Routes</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                        Add New Driver
                    </button>
                    <button class="btn" onclick="systemMaintenance()">System Maintenance</button>

                    <a href="{{ route('reports.generate.pdf') }}" class="btn btn-primary">
                        Generate PDF Report
                    </a>
                    <button class="btn" onclick="backupData()">Backup Data</button>
                    <a href="{{ route('user-management') }}" class="btn btn-primary">User Management</a></a>
                </div>
            </div>

            <div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDriverModalLabel">➕ Add New Driver</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form id="add-driver-form" action="{{ route('drivers.store') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="new-driver-name" class="form-label">Driver Name</label>
                                    <input type="text" class="form-control" id="new-driver-name" name="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new-driver-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="new-driver-email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="new-driver-phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="new-driver-phone" name="phone">
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Add Driver</button>
                                </div>
                            </form>
                        </div>

                    </div>
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
                                        $assignedTruck = $driver->truck; // returns the Truck model if assigned
                                    @endphp
                                    <option value="{{ $driver->id }}" {{ $assignedTruck ? 'disabled' : '' }}>
                                        {{ $driver->user->name ?? 'Unknown' }}
                                        @if($assignedTruck)
                                            (Truck ID: {{ $assignedTruck->truck_id }})
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
    <script>
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