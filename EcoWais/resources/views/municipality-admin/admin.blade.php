@extends('layouts.app')

@section('content')
<div id="admin-page" class="page">
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-white mb-2">Admin Dashboard</h2>
            <p class="text-white-50">Monitor and manage waste collection operations</p>
        </div>

        <!-- Success Alerts -->
        @if(session('truckSuccess'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('truckSuccess') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <!-- Quick Stats Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Trucks</h6>
                                <h2 class="fw-bold text-primary mb-0" id="total-trucks">{{ $trucks->count() }}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-truck text-primary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Active Collectors</h6>
                                <h2 class="fw-bold text-success mb-0" id="active-drivers">{{ $drivers->count() }}</h2>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-people-fill text-success fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Barangays</h6>
                                <h2 class="fw-bold text-info mb-0" id="registered-residents">{{ $locations->count() }}</h2>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-geo-alt-fill text-info fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                    <h6 class="text-muted mb-1 small">Total Waste Collected</h6>
                                    <h3 class="fw-bold text-primary mb-0">{{ $totalWaste }} kg</h3>
                                    <small class="text-muted">All Time</small>

                                </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-speedometer2 text-warning fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                    <div class="d-flex justify-content-end">
                        <button id="downloadReports" class="btn btn-success d-flex align-items-center mb-3"
                            style="border-width: 2px; border-radius: 0.5rem;">
                            <i class="bi bi-file-earmark-text-fill me-2"></i>
                            <span class="fw-semibold">Download PDF & Excel</span>
                        </button>
                    </div>


        <!-- Waste Collection Analytics -->
        <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i>Waste Collection Analytics</h5>
        <!-- Filter Dropdown -->
        <div class="d-flex align-items-center mb-3">
    <label for="chartFilter" class="me-2">Filter:</label>
    <select id="chartFilter" class="form-select w-auto">
        <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Today</option>
        <option value="weekly" {{ $filter === 'weekly' ? 'selected' : '' }}>This Week</option>
        <option value="monthly" {{ $filter === 'monthly' ? 'selected' : '' }}>This Month</option>
    </select>
</div>

    </div>

    <!-- Charts -->
    <div class="row g-4 mt-2">
        <!-- First Chart -->
        <div class="col-lg-6">
            <div class="card border-0 bg-light" style="height: 380px;">
                <div class="card-header bg-primary text-white border-0" style="padding: 0.75rem 1rem;">
                    <h6 class="mb-0" style="font-size: 1rem;">
                        <i class="bi bi-bar-chart-fill me-2"></i>Daily Waste Collection (kg)
                    </h6>
                </div>
                <div class="card-body p-3" style="height: calc(100% - 50px);">
                    <canvas id="dailyWasteChart" style="height: 100%; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Second Chart -->
        <div class="col-lg-6 d-flex justify-content-center">
            <div class="card border-0 bg-light" style="height: 380px; width: 90%;">
                <div class="card-header bg-success text-white border-0" style="padding: 0.75rem 1rem;">
                    <h6 class="mb-0" style="font-size: 1rem;">
                        <i class="bi bi-pie-chart-fill me-2"></i>Waste by Type
                    </h6>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="wasteTypeChart" style="height: 100%; width: 100%;"></canvas>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Reports Management</h5>
    </div>
    <div class="card-body">
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="barangay-tab" data-bs-toggle="tab" data-bs-target="#barangay-reports" type="button" role="tab">
                    <i class="bi bi-geo-alt-fill me-2"></i>Barangay Reports
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="driver-tab" data-bs-toggle="tab" data-bs-target="#driver-reports" type="button" role="tab">
                    <i class="bi bi-person-fill me-2"></i>Driver Reports
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="reportTabsContent">
            <!-- Barangay Reports Tab -->
            <div class="tab-pane fade show active" id="barangay-reports" role="tabpanel">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <select id="barangay-report-filter" class="form-select">
                            <option value="">All Reports</option>
                            <option value="new">New</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueManagementModal">
                            Manage Issues
                        </button>
                    </div>
                </div>

                @if(session('success1'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success1') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Time</th>
                                <th class="fw-semibold">Type</th>
                                <th class="fw-semibold">Location</th>
                                <th class="fw-semibold">Status</th>
                                <th class="fw-semibold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="barangay-admin-reports">
                            @foreach($reports as $report)
                                <tr>
                                    <td class="text-muted">{{ $report->created_at ? $report->created_at->format('M d, Y h:i A') : '' }}</td>
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
                                    <td>
                                        @php
                                            $status = $report->Status ?? 'Pending';
                                            $badgeClass = match($status) {
                                                'Resolved' => 'bg-success',
                                                'Pending'  => 'bg-warning text-dark',
                                                default    => 'bg-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('reports.resolve', $report->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="btn btn-sm btn-outline-success me-1"
                                                @if($report->Status === 'Resolved') disabled @endif
                                            >
                                                <i class="bi bi-check-circle-fill"></i> Resolve
                                            </button>
                                        </form>
                                        <button 
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewReportModal-{{ $report->id }}"
                                        >
                                            <i class="bi bi-eye-fill"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Driver Reports Tab -->
            <div class="tab-pane fade" id="driver-reports" role="tabpanel">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <select id="driver-report-filter" class="form-select">
                            <option value="">All Reports</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">ID</th>
                                <th class="fw-semibold">Driver</th>
                                <th class="fw-semibold">Issue Type</th>
                                <th class="fw-semibold">Description</th>
                                <th class="fw-semibold">Priority</th>
                                <th class="fw-semibold">Status</th>
                                <th class="fw-semibold">Date</th>
                                <th class="fw-semibold text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="driver-admin-reports">
                            @forelse($driverReports as $dReport)
                                <tr data-status="{{ strtolower($dReport->status ?? 'pending') }}" 
                                    data-priority="{{ strtolower($dReport->priority ?? 'low') }}">
                                    <td>#{{ $dReport->id }}</td>
                                    <td>
                                        @php
                                            $driver = $drivers->firstWhere('id', $dReport->driver_id);
                                            $driverName = $driver ? $driver->user->name : 'Unknown Driver';
                                        @endphp
                                        {{ $driverName }}
                                    </td>
                                    <td>
                                        @php
                                            $issueType = $dReport->issue_type ?? 'other';
                                            $badgeColor = match($issueType) {
                                                'vehicles' => 'bg-danger',
                                                'access' => 'bg-info',
                                                'schedule' => 'bg-warning text-dark',
                                                'equipment' => 'bg-primary',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeColor }}">
                                            {{ ucfirst(str_replace('-', ' ', $issueType)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" 
                                            style="max-width: 200px;" 
                                            title="{{ $dReport->description ?? 'No description' }}">
                                            {{ $dReport->description ?? 'No description' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $priority = $dReport->priority ?? 'low';
                                            $priorityBadge = match(strtolower($priority)) {
                                                'high' => 'bg-danger',
                                                'medium' => 'bg-warning text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $priorityBadge }}">
                                            {{ ucfirst($priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $status = $dReport->status ?? 'pending';
                                            $statusBadge = match(strtolower($status)) {
                                                'resolved' => 'bg-success',
                                                'in progress' => 'bg-info',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ $dReport->created_at ? $dReport->created_at->format('M d, Y g:i A') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('driver-reports.resolve', $dReport->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button 
                                                type="submit"
                                                class="btn btn-sm btn-outline-success me-1"
                                                @if(strtolower($dReport->status ?? '') === 'resolved') disabled @endif
                                            >
                                                <i class="bi bi-check-circle-fill"></i> Resolve
                                            </button>
                                        </form>
                                        <button 
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewDriverReportModal-{{ $dReport->id }}"
                                        >
                                            <i class="bi bi-eye-fill"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No driver reports found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Attendance Records</h5>
    <div>
        <a href="{{ route('attendance.export.pdf') }}" class="btn btn-danger btn-sm me-2">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
        </a>
        <a href="{{ route('attendance.export.csv') }}" class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export CSV
        </a>
    </div>
</div>



<div class="card-body p-3">
    <!-- Filters -->
    <div class="row mb-3 g-2">
        <div class="col-md-4">
            <input type="text" class="form-control" id="filter-driver" placeholder="Search Driver or Barangay">
        </div>
        <div class="col-md-4">
            <input type="date" class="form-control" id="filter-date" placeholder="Pickup Date">
        </div>
        <div class="col-md-4">
            <button type="button" id="clear-filters" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Clear
            </button>
        </div>
    </div>

    <!-- Results Info -->
    <div class="mb-2">
        <small class="text-muted">
            Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span id="total-records">0</span> records
            <span id="filtered-info" style="display: none;">(filtered from <span id="original-total">0</span> total records)</span>
        </small>
    </div>

    <div id="attendance-table">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th class="fw-semibold">Driver Name</th>
                        <th class="fw-semibold">Barangay</th>
                        <th class="fw-semibold">Pickup Date</th>
                        <th class="fw-semibold">Time In</th>
                        <th class="fw-semibold">Time Out</th>
                        <th class="fw-semibold">Hours Worked</th>
                        <th class="fw-semibold">Status</th>
                    </tr>
                </thead>
                <tbody id="attendance-tbody">
                    @forelse($attendance as $att)
                        @php
                            $hoursWorked = '-';
                            if ($att->time_in && $att->time_out) {
                                $hoursWorked = number_format(
                                    \Carbon\Carbon::parse($att->time_in)
                                        ->floatDiffInHours(\Carbon\Carbon::parse($att->time_out)),
                                    2
                                );
                            }
                        @endphp
                        <tr class="text-center align-middle" 
                            data-driver="{{ strtolower($att->user->name ?? 'unknown') }}"
                            data-barangay="{{ strtolower($att->location->location ?? 'unknown') }}"
                            data-timein="{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('Y-m-d') : '' }}">
                            <td>{{ $att->user->name ?? 'Unknown' }}</td>
                            <td>{{ $att->location->location ?? 'Unknown' }}</td>
                            <td>{{ $att->pickupSession ?? '-' }}</td>
                            <td>{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $hoursWorked }}</td>
                            <td>
                                @switch($att->status)
                                    @case('Present')
                                        <span class="badge bg-success">Present</span>
                                        @break
                                    @case('Late')
                                        <span class="badge bg-warning text-dark">Late</span>
                                        @break
                                    @case('Absent')
                                        <span class="badge bg-danger">Absent</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $att->status }}</span>
                                @endswitch
                            </td>
                        </tr>
                    @empty
                        <tr id="no-data-row">
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No attendance records found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Custom Pagination -->
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-end" id="pagination-controls">
                    <!-- Pagination buttons will be generated by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>

</div>
</div>

        <!-- Fleet Management -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="bi bi-truck-front-fill me-2"></i>Fleet Management</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <input type="text" id="fleet-search" class="form-control" placeholder="🔍 Search trucks or drivers...">
                    </div>
                    <div class="col-md-4">
                        <select id="fleet-status-filter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addTruckModal">
                            <i class="bi bi-plus-circle-fill me-2"></i>Add New Truck
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Truck ID</th>
                                <th class="fw-semibold">Driver</th>
                                <th class="fw-semibold">Location</th>
                                <th class="fw-semibold">Status</th>
                                <th class="fw-semibold">Fuel Level</th>
                                <th class="fw-semibold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="fleet-table">
                            @foreach($trucks as $truck)
                                <tr>
                                    <td class="fw-medium">{{ $truck->truck_id }}</td>
                                    <td>{{ $truck->driver->user->name ?? 'N/A' }}</td>
                                    <td class="text-muted">{{ $truck->initial_location }}</td>
                                    <td>
                                        @if(strtolower($truck->status) === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif(strtolower($truck->status) === 'inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                        @elseif(strtolower($truck->status) === 'maintenance')
                                            <span class="badge bg-warning">Maintenance</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($truck->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $truck->initial_fuel }}%" aria-valuenow="{{ $truck->initial_fuel }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="small">{{ $truck->initial_fuel }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTruckModal-{{ $truck->truck_id }}">
                                            <i class="bi bi-pencil-fill"></i> Edit
                                        </button>

                                        <button class="btn btn-sm btn-outline-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTruckModal-{{ $truck->truck_id }}">
                                            <i class="bi bi-eye-fill"></i> View
                                        </button>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>


                </div>
            </div>
        </div>
<!-- Edit Truck Modal (one per truck) -->
@foreach($trucks as $truck)
<div class="modal fade" id="editTruckModal-{{ $truck->truck_id }}" tabindex="-1" aria-labelledby="editTruckModalLabel-{{ $truck->truck_id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="editTruckModalLabel-{{ $truck->truck_id }}">
                    Edit Truck - {{ $truck->truck_id }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('trucks.update', $truck->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row g-3">
                            <!-- Truck ID -->
                            <div class="col-md-6">
                                <label for="truck_id_{{ $truck->truck_id }}" class="form-label">Truck ID</label>
                                <input type="text" name="truck_id" id="truck_id_{{ $truck->truck_id }}" class="form-control" value="{{ $truck->truck_id }}" required>
                            </div>
                            
                            <!-- Driver -->
                            <div class="col-md-6">
                                <label for="driver_{{ $truck->truck_id }}" class="form-label">Driver</label>
                                @php
                                    // Get all driver IDs that are already assigned to a truck (except current truck)
                                    $assignedDriverIds = $trucks->where('id', '!=', $truck->id)->pluck('driver_id')->filter()->toArray();
                                @endphp

                                <select name="driver_id" id="driver_{{ $truck->truck_id }}" class="form-select">
                                    <option value="">Select Driver</option>
                                    @foreach($drivers as $driver)
                                        @if(!in_array($driver->id, $assignedDriverIds) || $driver->id == $truck->driver_id)
                                            <option value="{{ $driver->id }}" @if($truck->driver_id == $driver->id) selected @endif>
                                                {{ $driver->user->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>

                            </div>
                            
                            <!-- Location -->
                            <div class="col-md-6">
                                <label for="location_{{ $truck->truck_id }}" class="form-label">Location</label>
                                <select name="initial_location" id="location_{{ $truck->truck_id }}" class="form-select">
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->location }}" 
                                            @if($truck->initial_location == $location->location) selected @endif>
                                            {{ $location->location }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            
                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status_{{ $truck->truck_id }}" class="form-label">Status</label>
                                <select name="status" id="status_{{ $truck->truck_id }}" class="form-select">
                                    <option value="active" @if($truck->status == 'active') selected @endif>Active</option>
                                    <option value="idle" @if($truck->status == 'idle') selected @endif>Idle</option>
                                    <option value="maintenance" @if($truck->status == 'maintenance') selected @endif>Maintenance</option>
                                </select>
                            </div>
                            
                            <!-- Fuel Level -->
                            <div class="col-md-6">
                                <label for="fuel_{{ $truck->truck_id }}" class="form-label">Fuel Level (%)</label>
                                <input type="number" name="initial_fuel" id="fuel_{{ $truck->truck_id }}" class="form-control" value="{{ $truck->initial_fuel }}" min="0" max="100">
                            </div>

                            <!-- Optional extra field (example) -->
                            <div class="col-md-6">
                                <label for="extra_{{ $truck->truck_id }}" class="form-label">Notes</label>
                                <input type="text" name="notes" id="extra_{{ $truck->truck_id }}" class="form-control" placeholder="Optional notes">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
<!-- Pending Reports -->
        
        </div>

<!-- view modal -->

@foreach($reports as $report)
<div class="modal fade" id="viewReportModal-{{ $report->id }}" tabindex="-1" aria-labelledby="viewReportLabel-{{ $report->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="viewReportLabel-{{ $report->id }}">
                    Report Details - {{ $report->created_at? $report->created_at->format('M d, Y h:i A') : '' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-3">

                        <!-- Issue Type -->
                        <div class="col-md-6">
                            <label class="fw-semibold">Issue Type</label>
                            <input type="text" class="form-control" 
                                value="@php
                                    if ($report->issue_type === 'other') {
                                        echo $report->other_issue ?? '';
                                    } elseif ($report->issue_type === 'driver-absent') {
                                        echo 'Absent - ' . ($report->driver->user->name ?? 'Unknown');
                                    } else {
                                        echo $report->issue_type;
                                    }
                                @endphp"
                                readonly>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="fw-semibold">Location</label>
                            <input type="text" class="form-control" value="{{ $report->location }}" readonly>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <label class="fw-semibold">Priority</label>
                            <input type="text" class="form-control" value="{{ ucfirst($report->priority) }}" readonly>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="fw-semibold">Status</label>
                            <input type="text" class="form-control" value="{{ $report->Status }}" readonly>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="fw-semibold">Description</label>
                            <textarea class="form-control" rows="3" readonly>{{ $report->description ?? 'No description provided.' }}</textarea>
                        </div>

                        <!-- Image Placeholder -->
                        <div class="col-12 text-center mt-3">
                            <label class="fw-semibold mb-2 d-block">Evidence</label>

                            <div class="border rounded p-3" style="width: 100%; max-width: 400px; margin: auto;">
                                @if($report->photo_path)
                                    <img src="{{ asset($report->photo_path) }}" 
                                         class="img-fluid rounded" alt="Report Image">
                                @else
                                    <div class="d-flex justify-content-center align-items-center"
                                        style="height: 200px; background: #f8f9fa; border-radius: 10px;">
                                        <span class="text-muted">No image available</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endforeach

<!-- Issue Management Modal -->
<div class="modal fade" id="issueManagementModal" tabindex="-1" aria-labelledby="issueManagementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="issueManagementLabel">Issue Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Issue Button -->
                <div class="mb-3 text-end">
                    <button class="btn btn-success" id="addIssueBtn">Add New Issue</button>
                </div>

                <!-- Issues Table -->
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Issue Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="issuesTableBody">
                        <!-- Example row: This should be populated dynamically -->
                        <!--
                        <tr>
                            <td>1</td>
                            <td>Missed Collection</td>
                            <td>
                                <button class="btn btn-danger btn-sm deleteIssueBtn">Delete</button>
                            </td>
                        </tr>
                        -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<!-- Add Truck Modal -->
<div class="modal fade" id="addTruckModal" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addTruckModalLabel">
                    <i class="bi bi-plus-circle-fill me-2"></i>Add New Truck
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('trucks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="truck_id" class="form-label fw-semibold">Truck ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="truck_id" name="truck_id" required>
                    </div>

                    <div class="mb-3">
                        <label for="driver_id" class="form-label fw-semibold">Driver Name <small class="text-muted">(Optional)</small></label>
                        <select name="driver_id" class="form-select">
                            <option value="">No Driver Assigned</option>
                            @foreach($drivers as $driver)
                                @php
                                    $assignedTruck = $driver->truck;
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
                        <small class="text-muted">Drivers already assigned or deactivated are disabled</small>
                    </div>

                    <div class="mb-3">
                        <label for="initial_location" class="form-label fw-semibold">Initial Location <span class="text-danger">*</span></label>
                        <select class="form-select" id="initial_location" name="initial_location" required>
                            <option value="">-- Select Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->location }}">{{ $location->location }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="initial_fuel" class="form-label fw-semibold">Initial Fuel (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="initial_fuel" name="initial_fuel" min="0" max="100" value="100" required>
                        <div class="form-text">Enter fuel level from 0 to 100</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle-fill me-1"></i>Add Truck
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@foreach($trucks as $truck)
<div class="modal fade" id="viewTruckModal-{{ $truck->truck_id }}" tabindex="-1" aria-labelledby="viewTruckLabel-{{ $truck->truck_id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-sm">
            
            {{-- Header --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewTruckLabel-{{ $truck->truck_id }}">
                    <i class="bi bi-truck-front-fill me-2"></i>Truck Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body">
                {{-- Truck Info --}}
                <div class="mb-3">
                    <p class="mb-1"><strong>Truck ID:</strong> <span class="text-primary">{{ $truck->truck_id }}</span></p>
                    <p class="mb-0"><strong>Driver:</strong> {{ $truck->driver->user->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Status:</strong> 
                        @if(strtolower($truck->status) === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif(strtolower($truck->status) === 'inactive')
                            <span class="badge bg-secondary">Inactive</span>
                        @elseif(strtolower($truck->status) === 'maintenance')
                            <span class="badge bg-warning text-dark">Maintenance</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($truck->status) }}</span>
                        @endif
                    </p>
                </div>

                {{-- Pickups --}}
                @if($truck->pickups && count($truck->pickups) > 0)
                    <p class="mb-2"><strong>Pickup Locations:</strong></p>
                    <ul class="list-group list-group-flush">
                        @foreach($truck->pickups as $index => $pickup)
                            <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start pickup-item"
                                data-lat="{{ $pickup['lat'] }}"
                                data-lng="{{ $pickup['lng'] }}">
                                
                                <div class="mb-2 mb-md-0">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                    <span class="pickup-address">Loading address...</span>
                                </div>

                                <div>
                                    <i class="bi bi-clock-fill text-secondary me-1"></i>
                                    <small>{{ $pickup['timeWindow']['start'] }} - {{ $pickup['timeWindow']['end'] }}</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">No pickups found for this truck.</p>
                @endif
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Close
                </button>
            </div>

        </div>
    </div>
</div>
@endforeach


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    document.addEventListener('DOMContentLoaded', function() {
    // Barangay Reports Filter
    const barangayReportFilter = document.getElementById('barangay-report-filter');
    const barangayReportsTable = document.getElementById('barangay-admin-reports');
    
    if (barangayReportFilter && barangayReportsTable) {
        barangayReportFilter.addEventListener('change', function() {
            filterReports(this.value, barangayReportsTable, 3); // Status is at index 3
        });
    }

    // Driver Reports Filters
    const driverReportFilter = document.getElementById('driver-report-filter');
    const priorityFilter = document.getElementById('priority-filter');
    const driverReportsTable = document.getElementById('driver-admin-reports');
    
    if (driverReportFilter && driverReportsTable) {
        driverReportFilter.addEventListener('change', function() {
            filterDriverReports();
        });
    }

    if (priorityFilter && driverReportsTable) {
        priorityFilter.addEventListener('change', function() {
            filterDriverReports();
        });
    }

    function filterReports(filterValue, tableBody, statusColumnIndex) {
        const rows = tableBody.getElementsByTagName('tr');
        let visibleCount = 0;
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const statusCell = row.cells[statusColumnIndex];
            
            if (statusCell) {
                const statusBadge = statusCell.querySelector('.badge');
                const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                
                if (filterValue === '' || filterValue.toLowerCase() === statusText) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
        }

        showNoResultsMessage(visibleCount, tableBody, statusColumnIndex === 3 ? 5 : 8);
    }

    function filterDriverReports() {
        const statusFilter = driverReportFilter.value.toLowerCase();
        const priority = priorityFilter.value.toLowerCase();
        const rows = driverReportsTable.getElementsByTagName('tr');
        let visibleCount = 0;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowStatus = row.getAttribute('data-status') || '';
            const rowPriority = row.getAttribute('data-priority') || '';

            const matchesStatus = statusFilter === '' || rowStatus === statusFilter;
            const matchesPriority = priority === '' || rowPriority === priority;

            if (matchesStatus && matchesPriority) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        }

        showNoResultsMessage(visibleCount, driverReportsTable, 8);
    }

    function showNoResultsMessage(visibleCount, tableBody, colspan) {
        let noResultsRow = tableBody.querySelector('#no-results-row');
        
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="${colspan}" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No reports found matching the filters
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else {
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('filter-driver');
    const dateInput = document.getElementById('filter-date');
    const clearBtn = document.getElementById('clear-filters');
    const tbody = document.getElementById('attendance-tbody');
    const paginationControls = document.getElementById('pagination-controls');
    
    const showingStart = document.getElementById('showing-start');
    const showingEnd = document.getElementById('showing-end');
    const totalRecords = document.getElementById('total-records');
    const filteredInfo = document.getElementById('filtered-info');
    const originalTotal = document.getElementById('original-total');

    let allRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.cells.length >= 7);
    let filteredRows = [...allRows];
    let currentPage = 1;
    const rowsPerPage = 10;

    originalTotal.textContent = allRows.length;

    function filterRows() {
        const searchVal = searchInput.value.toLowerCase().trim();
        const dateVal = dateInput.value;

        filteredRows = allRows.filter(row => {
            const driver = row.getAttribute('data-driver') || '';
            const barangay = row.getAttribute('data-barangay') || '';
            const timeIn = row.getAttribute('data-timein') || '';

            const matchesSearch = searchVal === '' || 
                                  driver.includes(searchVal) || 
                                  barangay.includes(searchVal);
            
            const matchesDate = dateVal === '' || timeIn.includes(dateVal);

            return matchesSearch && matchesDate;
        });

        currentPage = 1; // Reset to first page when filtering
        updateDisplay();
    }

    function updateDisplay() {
        const totalFiltered = filteredRows.length;
        const totalPages = Math.ceil(totalFiltered / rowsPerPage);
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, totalFiltered);

        // Hide all rows first
        allRows.forEach(row => row.style.display = 'none');

        // Show only current page rows
        if (totalFiltered > 0) {
            for (let i = startIndex; i < endIndex; i++) {
                filteredRows[i].style.display = '';
            }
            hideNoResults();
            
            // Update info text
            showingStart.textContent = startIndex + 1;
            showingEnd.textContent = endIndex;
            totalRecords.textContent = totalFiltered;
            
            if (totalFiltered < allRows.length) {
                filteredInfo.style.display = '';
            } else {
                filteredInfo.style.display = 'none';
            }
        } else {
            showNoResults();
            showingStart.textContent = 0;
            showingEnd.textContent = 0;
            totalRecords.textContent = 0;
            filteredInfo.style.display = 'none';
        }

        // Update pagination
        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
        paginationControls.appendChild(prevLi);

        // Page numbers
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);

        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            const firstLi = document.createElement('li');
            firstLi.className = 'page-item';
            firstLi.innerHTML = `<a class="page-link" href="#" data-page="1">1</a>`;
            paginationControls.appendChild(firstLi);

            if (startPage > 2) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                paginationControls.appendChild(dotsLi);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            paginationControls.appendChild(li);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dotsLi = document.createElement('li');
                dotsLi.className = 'page-item disabled';
                dotsLi.innerHTML = `<span class="page-link">...</span>`;
                paginationControls.appendChild(dotsLi);
            }

            const lastLi = document.createElement('li');
            lastLi.className = 'page-item';
            lastLi.innerHTML = `<a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>`;
            paginationControls.appendChild(lastLi);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
        paginationControls.appendChild(nextLi);

        // Attach click events
        paginationControls.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    updateDisplay();
                }
            });
        });
    }

    function showNoResults() {
        let noResultsRow = document.getElementById('no-results-row');
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    No matching records found
                </td>
            `;
            tbody.appendChild(noResultsRow);
        }
        noResultsRow.style.display = '';
        
        // Hide the "no data" row if it exists
        const noDataRow = document.getElementById('no-data-row');
        if (noDataRow) noDataRow.style.display = 'none';
    }

    function hideNoResults() {
        const noResultsRow = document.getElementById('no-results-row');
        if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }

    // Event listeners
    searchInput.addEventListener('input', filterRows);
    dateInput.addEventListener('change', filterRows);
    
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        dateInput.value = '';
        filterRows();
    });

    // Initial display
    updateDisplay();
});
document.addEventListener('DOMContentLoaded', function() {
    const reportFilter = document.getElementById('report-filter');
    const reportsTable = document.getElementById('admin-reports');
    
    if (reportFilter && reportsTable) {
        reportFilter.addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = reportsTable.getElementsByTagName('tr');
            let visibleCount = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const statusCell = row.cells[3]; // Status column is 4th column (index 3)
                
                if (statusCell) {
                    const statusBadge = statusCell.querySelector('.badge');
                    const statusText = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
                    
                    // Show all reports if no filter selected
                    if (filterValue === '') {
                        row.style.display = '';
                        visibleCount++;
                        continue;
                    }
                    
                    // Match filter value to status text
                    let shouldShow = false;
                    
                    if (filterValue === statusText) {
                        shouldShow = true;
                    }
                    
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                }
            }

            // Show "no results" message if no rows are visible
            showNoResultsMessage(visibleCount, reportsTable);
        });
    }

    function showNoResultsMessage(visibleCount, tableBody) {
        let noResultsRow = document.getElementById('no-results-row');
        
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No reports found for this status
                    </td>
                `;
                tableBody.appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else {
            if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }
    }
});


document.addEventListener('DOMContentLoaded', () => {
    const modals = document.querySelectorAll('.modal');

    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', async () => {
            const pickupItems = modal.querySelectorAll('.pickup-item');

            pickupItems.forEach(async item => {
                const lat = item.dataset.lat;
                const lng = item.dataset.lng;
                const addressSpan = item.querySelector('.pickup-address'); // update only this span

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
                    const data = await res.json();
                    const address = data.display_name || `${lat}, ${lng}`;
                    addressSpan.textContent = address;
                } catch (err) {
                    addressSpan.textContent = `${lat}, ${lng}`;
                }
            });
        });
    });
});

document.getElementById('chartFilter').addEventListener('change', function() {
    const value = this.value;
    const url = new URL(window.location.href);
    url.searchParams.set('filter', value);
    window.location.href = url.toString();
});

document.addEventListener('DOMContentLoaded', function() {

    const issuesTableBody = document.getElementById('issuesTableBody');
    const addIssueBtn = document.getElementById('addIssueBtn');

    // --- Fetch and render issues from DB ---
    function loadIssues() {
        fetch("{{ route('issues.get') }}")
            .then(res => res.json())
            .then(data => {
                issuesTableBody.innerHTML = '';
                data.forEach((issue, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${issue.issue_name}</td>
                        <td>
                            <button class="btn btn-danger btn-sm deleteIssueBtn" data-id="${issue.id}">Delete</button>
                        </td>
                    `;
                    issuesTableBody.appendChild(row);
                });
                attachDeleteEvents();
            });
    }

    // --- Delete issue ---
    function attachDeleteEvents() {
        document.querySelectorAll('.deleteIssueBtn').forEach(btn => {
            btn.onclick = function() {
                if(confirm('Are you sure you want to delete this issue?')) {
                    const id = this.dataset.id;
                    fetch(`/issues/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => loadIssues());
                }
            }
        });
    }

    // --- Add new issue row inline ---
    addIssueBtn.onclick = function() {
        // prevent multiple input rows
        if(document.querySelector('#issuesTableBody input')) return;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#</td>
            <td><input type="text" class="form-control form-control-sm" placeholder="Enter issue name"></td>
            <td>
                <button class="btn btn-success btn-sm" id="saveNewIssue">Save</button>
                <button class="btn btn-secondary btn-sm" id="cancelNewIssue">Cancel</button>
            </td>
        `;
        issuesTableBody.prepend(row);

        const saveBtn = row.querySelector('#saveNewIssue');
        const cancelBtn = row.querySelector('#cancelNewIssue');
        const input = row.querySelector('input');

        saveBtn.onclick = function() {
            const value = input.value.trim();
            if(!value) { alert('Issue name cannot be empty!'); return; }

            fetch("{{ route('issues.add') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({issue_name: value})
            }).then(res => res.json())
              .then(() => loadIssues());
        };

        cancelBtn.onclick = function() {
            row.remove();
        };
    }

    // --- Load issues when modal opens ---
    const modal = document.getElementById('issueManagementModal');
    modal.addEventListener('show.bs.modal', loadIssues);
});

    document.addEventListener('DOMContentLoaded', function() {
    const downloadBtn = document.getElementById('downloadReports');
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            console.log('Download button clicked');
            
            // Create hidden iframe for PDF download
            const pdfIframe = document.createElement('iframe');
            pdfIframe.style.display = 'none';
            pdfIframe.src = "{{ route('reports.generate.pdf') }}";
            pdfIframe.onload = function() {
                console.log('PDF iframe loaded');
            };
            pdfIframe.onerror = function() {
                console.error('PDF iframe error');
            };
            document.body.appendChild(pdfIframe);
            
            // Create hidden iframe for Excel download after delay
            setTimeout(() => {
                console.log('Starting Excel download');
                const excelIframe = document.createElement('iframe');
                excelIframe.style.display = 'none';
                excelIframe.src = "{{ route('reports.generate.excel') }}";
                excelIframe.onload = function() {
                    console.log('Excel iframe loaded');
                };
                excelIframe.onerror = function() {
                    console.error('Excel iframe error');
                };
                document.body.appendChild(excelIframe);
                
                // Clean up iframes after downloads complete
                setTimeout(() => {
                    if (document.body.contains(pdfIframe)) {
                        document.body.removeChild(pdfIframe);
                    }
                    if (document.body.contains(excelIframe)) {
                        document.body.removeChild(excelIframe);
                    }
                    console.log('Cleanup complete');
                }, 5000);
            }, 1000);
        });
    } else {
        console.error('Download button not found!');
    }
});
// Daily Waste Chart
const dailyCtx = document.getElementById('dailyWasteChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($dailyLabels) !!},
        datasets: [{
            label: 'Waste Collected (kg)',
            data: {!! json_encode($dailyData) !!},
            backgroundColor: '#4dabf7',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            x: { 
                title: { display: true, text: 'Date' },
                grid: { display: false }
            },
            y: { 
                title: { display: true, text: 'Kg' },
                beginAtZero: true
            }
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
            backgroundColor: ['#f06595', '#51cf66', '#fcc419', '#339af0']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '55%',
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Map Initialization
document.addEventListener('DOMContentLoaded', function () {
    let map;
    let marker;

    const modalEl = document.getElementById('manageLocationsModal');

    modalEl.addEventListener('shown.bs.modal', function () {
        if (!map) {
            map = L.map('map').setView([13.411, 121.180], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng, { draggable: true }).addTo(map);

                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);

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

                marker.on('dragend', function(ev) {
                    const p = ev.target.getLatLng();
                    document.getElementById('latitude').value = p.lat.toFixed(6);
                    document.getElementById('longitude').value = p.lng.toFixed(6);

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

        setTimeout(function() {
            map.invalidateSize();
        }, 200);
    });
});

// Fleet Search and Filter
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