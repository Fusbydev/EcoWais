@extends('layouts.app')
@section('content')

<style>
    /* Mobile-first responsive enhancements */
    @media (max-width: 767.98px) {
        /* Reduce padding on mobile */
        .container-fluid {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        /* Stack cards vertically with proper spacing */
        .row.g-4 {
            gap: 1rem !important;
        }

        /* Make header text smaller on mobile */
        .text-center h2 {
            font-size: 1.5rem;
        }

        .text-center p {
            font-size: 0.875rem;
        }

        /* Optimize card spacing */
        .card {
            margin-bottom: 1rem;
        }

        /* Make badges wrap properly */
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Improve form layout on mobile */
        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.5rem;
        }

        /* Make tables horizontally scrollable */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Adjust table font size */
        .table {
            font-size: 0.875rem;
        }

        /* Stack attendance cards in single column */
        .attendance-summary .col-12 {
            margin-bottom: 0.75rem;
        }

        /* Optimize modal for mobile */
        .modal-xl {
            max-width: 95%;
        }

        /* Make stat cards more compact */
        .card-body h2 {
            font-size: 1.75rem;
        }

        /* Improve button sizing on mobile */
        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }

    /* Tablet adjustments */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .container-fluid {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }

        /* Two-column layout for attendance cards on tablet */
        .attendance-summary .col-12 {
            flex: 0 0 auto;
            width: 50%;
        }
    }

    /* Ensure proper hierarchy */
    .card-header h5 {
        font-size: clamp(1rem, 2.5vw, 1.25rem);
    }

    /* Responsive table actions */
    @media (max-width: 575.98px) {
        .table td, .table th {
            padding: 0.5rem 0.25rem;
        }

        /* Stack form columns on extra small screens */
        .col-md-6, .col-md-12 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }
</style>

<div id="resident-page" class="page">
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="text-center mb-4 mb-md-5">
            <h2 class="fw-bold text-white mb-2">Barangay Admin Portal</h2>
            <p class="text-white-50">Manage attendance, track reports, and monitor barangay operations</p>
        </div>

        <div class="row g-3 g-md-4">
            <!-- Left Column -->
            <div class="col-12 col-lg-4 order-1 order-lg-1">
                <!-- Barangay Information Card -->
                <div class="card shadow-sm border-0 mb-3 mb-md-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Barangay Information</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $adminLocation = $locations->firstWhere('adminId', session('user_id'));
                            $totalCollectors = $collectors1->where('initial_location', $adminLocation->location)->count();
                        @endphp

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">BARANGAY NAME</label>
                            <h4 class="text-primary mb-0">{{ $adminLocation->location ?? '—' }}</h4>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                            <span class="text-muted mb-2 mb-sm-0"><i class="bi bi-people-fill me-2"></i>Total Collectors</span>
                            <span class="badge bg-primary rounded-pill fs-6">{{ $totalCollectors }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-semibold">ASSIGNED DRIVERS</label>
                            <p class="mb-0">
                                @if($truckData->count() > 0)
                                    {{ implode(', ', $truckData->pluck('name')->toArray()) }}
                                @else
                                    <span class="text-muted">No drivers assigned</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="form-label text-muted small fw-semibold">PICKUP SCHEDULE</label>
                            <p class="mb-0">
                                @if($pickupDates->count() > 0)
                                    @foreach($pickupDates->toArray() as $date)
                                        <span class="badge bg-success me-1 mb-1">{{ $date }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No scheduled pickups</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div class="row g-2 g-md-3 attendance-summary">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 border-start border-success border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Present Today</h6>
                                        <h2 class="text-success mb-0 fw-bold" id="present-count">{{ $present }}</h2>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-2 p-md-3 rounded">
                                        <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 border-start border-danger border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Absent Today</h6>
                                        <h2 class="text-danger mb-0 fw-bold" id="absent-count">{{ $absent }}</h2>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 p-2 p-md-3 rounded">
                                        <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0 border-start border-warning border-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Late Arrivals</h6>
                                        <h2 class="text-warning mb-0 fw-bold" id="late-count">{{ $late }}</h2>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-2 p-md-3 rounded">
                                        <i class="bi bi-clock-fill text-warning fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-8 order-2 order-lg-2">
                <!-- Driver/Collector Attendance -->
                <div class="card shadow-sm border-0 mb-3 mb-md-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-2"><i class="bi bi-person-badge-fill me-2"></i>Driver/Collector Attendance</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 shadow-sm">
                            <div class="d-flex flex-column flex-sm-row">
                                <div class="me-0 me-sm-3 mb-2 mb-sm-0">
                                    <i class="bi bi-info-circle-fill fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading">How to Mark Attendance</h6>
                                    <ul class="mb-0 small">
                                        <li>Click "Time In" to record when a driver/collector starts their shift for today's pickup</li>
                                        <li>Click "Time Out" to record when they finish their shift for the current pickup</li>
                                        <li>Attendance will automatically be marked as "Present", "Late", or "Absent" based on arrival time</li>
                                        <li>Standard shift: 1:00 AM - 5:00 PM. Arrivals after 7:00 AM are considered Late</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            @php
                                use Carbon\Carbon;
                                $today = Carbon::now('Asia/Manila')->toDateString();
                            @endphp
                            <table class="table table-hover align-middle" id="trucks-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">Name</th>
                                        <th class="fw-semibold d-none d-sm-table-cell">Truck ID</th>
                                        <th class="fw-semibold">Time In</th>
                                        <th class="fw-semibold">Time Out</th>
                                        <th class="fw-semibold d-none d-md-table-cell">Hours</th>
                                        <th class="fw-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="trucks-tbody">
                                    @forelse($truckData as $truck)
                                        @php
                                            $hasTodayPickup = collect($truck['sessionPickups'])->contains($today);
                                        @endphp
                                        @if($hasTodayPickup)
                                        <tr>
                                            <td class="fw-medium">{{ $truck['name'] }}</td>
                                            <td class="text-muted d-none d-sm-table-cell">{{ $truck['truck_id'] }}</td>
                                            <td>
                                                @if($truck['time_in'] === '-')
                                                    <form action="{{ route('attendance.timein') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $truck['driver_user_id'] }}">
                                                        <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                                                        <input type="hidden" name="session_pickup" value="{{ $today }}">
                                                        <label class="btn btn-sm btn-success d-inline-flex align-items-center" style="gap:4px; cursor:pointer;">
                                                            <input type="checkbox" name="TimeIn" onchange="this.form.submit();" class="form-check-input m-0" />
                                                            <span class="d-none d-sm-inline">Time In</span>
                                                            <i class="bi bi-clock d-sm-none"></i>
                                                        </label>
                                                    </form>
                                                @else
                                                    <span class="text-success fw-medium small">{{ $truck['time_in'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($truck['time_out'] === '-')
                                                    <form action="{{ route('attendance.timeout') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $truck['driver_user_id'] }}">
                                                        <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <span class="d-none d-sm-inline">Time Out</span>
                                                            <i class="bi bi-clock-history d-sm-none"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-warning fw-medium small">{{ $truck['time_out'] }}</span>
                                                @endif
                                            </td>
                                            <td class="text-muted d-none d-md-table-cell">{{ $truck['hours_worked'] }}</td>
                                            <td>
                                                @if($truck['status'] === 'Present')
                                                    <span class="badge bg-success">Present</span>
                                                @elseif($truck['status'] === 'Late')
                                                    <span class="badge bg-warning">Late</span>
                                                @elseif($truck['status'] === 'Absent')
                                                    <span class="badge bg-danger">Absent</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $truck['status'] }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                                Select a barangay to see assigned trucks
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Report an Issue -->
                <div class="card shadow-sm border-0 mb-3 mb-md-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0"><i class="bi bi-megaphone-fill me-2"></i>Report an Issue</h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form id="report-form" method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-2 g-md-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Issue Type <span class="text-danger">*</span></label>
                                    <select id="issue-type" name="issue_type" class="form-select" required>
                                        <option value="">Select issue type</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6" id="driver-container" style="display:none;">
                                    <label for="driver-id" class="form-label fw-semibold">Select Driver</label>
                                </div>

                                <input type="hidden" value="{{ session('user_id') }}" name="adminId">

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Location (Street/Area) <span class="text-danger">*</span></label>
                                    <input type="text" id="issue-location" name="location" class="form-control" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" id="issue-datetime" name="incident_datetime" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Priority Level <span class="text-danger">*</span></label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="priority" id="priority-low" value="low" required>
                                        <label class="btn btn-outline-success" for="priority-low">Low</label>

                                        <input type="radio" class="btn-check" name="priority" id="priority-medium" value="medium" checked>
                                        <label class="btn btn-outline-warning" for="priority-medium">Medium</label>

                                        <input type="radio" class="btn-check" name="priority" id="priority-high" value="high">
                                        <label class="btn btn-outline-danger" for="priority-high">High</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                                    <textarea id="issue-description" name="description" class="form-control" rows="4" required></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Attach Photo (Optional)</label>
                                    <input type="file" id="issue-photo" name="photo" class="form-control" accept="image/*">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning w-100 py-2">
                                        <i class="bi bi-send-fill me-2"></i>Submit Report
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Submitted Reports History -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <h5 class="mb-0"><i class="bi bi-file-text-fill me-2"></i>Submitted Reports</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="exportReports()">
                                <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Export</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select id="report-status-filter" class="form-select" onchange="filterReports()">
                                <option value="all">All Reports</option>
                                <option value="pending">Pending</option>
                                <option value="in-review">In Review</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">ID</th>
                                        <th class="fw-semibold d-none d-md-table-cell">Date</th>
                                        <th class="fw-semibold">Issue Type</th>
                                        <th class="fw-semibold d-none d-lg-table-cell">Location</th>
                                        <th class="fw-semibold">Priority</th>
                                        <th class="fw-semibold d-none d-sm-table-cell">Status</th>
                                        <th class="fw-semibold">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="reports-history">
                                    @foreach($reports as $report)
                                        <tr>
                                            <td class="text-muted">#{{ $report->id }}</td>
                                            <td class="d-none d-md-table-cell small">{{ \Carbon\Carbon::parse($report->incident_datetime)->format('M d, Y h:i A') }}</td>
                                            <td class="small">
                                                @php
                                                    if ($report->issue_type === 'other') {
                                                        $issueDisplay = $report->other_issue;
                                                    } elseif ($report->issue_type === 'driver-absent') {
                                                        $driverName = $report->driver && $report->driver->user 
                                                                    ? $report->driver->user->name 
                                                                    : 'unknown';
                                                        $issueDisplay = "absent-" . $driverName;
                                                    } else {
                                                        $issueDisplay = $report->issue_type;
                                                    }
                                                @endphp
                                                {{ $issueDisplay }}
                                            </td>
                                            <td class="d-none d-lg-table-cell small">{{ $report->location }}</td>
                                            <td>
                                                @if(strtolower($report->priority) === 'high')
                                                    <span class="badge bg-danger">High</span>
                                                @elseif(strtolower($report->priority) === 'medium')
                                                    <span class="badge bg-warning">Medium</span>
                                                @else
                                                    <span class="badge bg-success">Low</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-sm-table-cell"><span class="badge bg-info">Pending</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewReportModal{{ $report->id }}">
                                                    <i class="bi bi-eye-fill"></i><span class="d-none d-sm-inline ms-1">View</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modals -->
@foreach($reports as $report)
<div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="viewReportLabel{{ $report->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewReportLabel{{ $report->id }}">
                    <i class="bi bi-file-text-fill me-2"></i>Report Details - #{{ $report->id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-3 g-md-4">
                    <div class="col-12 col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3 text-primary">
                                    <i class="bi bi-info-circle-fill me-2"></i>Report Information
                                </h6>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Issue Type</small>
                                    <p class="mb-0 fw-medium">
                                        {{ $report->issue_type === 'other' ? $report->other_issue : ($report->issue_type === 'driver-absent' && $report->driver && $report->driver->user ? "absent-" . $report->driver->user->name : $report->issue_type) }}
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Location</small>
                                    <p class="mb-0 fw-medium">{{ $report->location }}</p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Priority</small>
                                    @if(strtolower($report->priority) === 'high')
                                        <span class="badge bg-danger">{{ ucfirst($report->priority) }}</span>
                                    @elseif(strtolower($report->priority) === 'medium')
                                        <span class="badge bg-warning text-dark">{{ ucfirst($report->priority) }}</span>
                                    @else
                                        <span class="badge bg-success">{{ ucfirst($report->priority) }}</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge bg-info">Pending</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Submitted At</small>
                                    <p class="mb-0 fw-medium">{{ $report->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3 text-primary">
                                    <i class="bi bi-card-text me-2"></i>Details & Image
                                </h6>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Description</small>
                                    <p class="mb-0">{{ $report->description }}</p>
                                </div>
                                @if($report->photo_path)
                                    <div class="text-center mt-3">
                                        <img src="{{ asset($report->photo_path) }}" alt="Report Image" class="img-fluid rounded shadow" style="max-height:300px;">
                                    </div>
                                @else
                                    <div class="text-center py-4 py-md-5 text-muted">
                                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No photo submitted</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

@php
$drivers = $truckData->map(function($d) {
    return [
        'user_id' => $d['driver_user_id'],
        'driver_name' => $d['name'],
        'status' => $d['status'] ?? 'Not Recorded',
        'truck_id' => $d['truck_id'] ?? null
    ];
});
@endphp

<script>
document.addEventListener("DOMContentLoaded", function() {
    const select = document.getElementById('issue-type');

    fetch('{{ route("issues.get") }}')
        .then(response => response.json())
        .then(data => {
            // Clear current options except the placeholder
            select.innerHTML = '<option value="">Select issue type</option>';
            
            // Append options from DB
            data.forEach(issue => {
                const option = document.createElement('option');
                option.value = issue.issue_name;
                option.text = issue.issue_name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching issues:', error));
});

document.addEventListener("DOMContentLoaded", function () {
    const issueType = document.getElementById("issue-type");
    const driverContainer = document.getElementById("driver-container");

    const driverDropdown = document.createElement("select");
    driverDropdown.id = "driver-id";
    driverDropdown.name = "driver_id";
    driverDropdown.classList.add("form-control");
    driverContainer.appendChild(driverDropdown);

    const drivers = @json($drivers);

    drivers.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d.user_id;
        opt.textContent = d.driver_name + (d.truck_id ? ` (Truck ID: ${d.truck_id})` : '');
        driverDropdown.appendChild(opt);
    });

    const otherInput = document.createElement("input");
    otherInput.type = "text";
    otherInput.id = "other-issue";
    otherInput.name = "other_issue";
    otherInput.placeholder = "Specify the issue";
    otherInput.style.display = "none";
    otherInput.classList.add("form-control");
    issueType.parentNode.appendChild(otherInput);

    issueType.addEventListener("change", function () {
        if (this.value === "other") {
            otherInput.style.display = "block";
            driverContainer.style.display = "none";
        } else if (this.value === "driver-absent") {
            driverContainer.style.display = "block";
            otherInput.style.display = "none";
        } else {
            driverContainer.style.display = "none";
            otherInput.style.display = "none";
        }
    });
});
</script>

@endsection