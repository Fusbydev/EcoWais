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

                <div class="card shadow-lg border-0 mb-3 mb-md-4 overflow-hidden">
    <div class="card-header position-relative py-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div class="d-flex align-items-center">
            <div class="bg-white bg-opacity-25 rounded-circle p-2 me-2">
                <i class="bi bi-megaphone-fill fs-6 text-white"></i>
            </div>
            <h6 class="mb-0 text-white fw-bold">Report an Issue</h6>
        </div>
    </div>
    
    <div class="card-body p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #10b98115 0%, #05966915 100%); border-left: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2" style="color: #10b981;"></i>
                    <span class="flex-grow-1 small">{{ session('success') }}</span>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #ef444415 0%, #dc262615 100%); border-left: 4px solid #ef4444 !important;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="color: #ef4444;"></i>
                    <span class="flex-grow-1 small">{{ session('error') }}</span>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: linear-gradient(135deg, #ef444415 0%, #dc262615 100%); border-left: 4px solid #ef4444 !important;">
                <div class="d-flex">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="color: #ef4444;"></i>
                    <ul class="mb-0 flex-grow-1 small">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        <form id="report-form" method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 g-md-4">
                <!-- Issue Type -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-exclamation-circle me-1"></i>Issue Type <span class="text-danger">*</span>
                    </label>
                    <select id="issue-type" name="issue_type" class="form-select border-2 shadow-sm" required style="border-color: #f59e0b30;">
                        <option value="">Select issue type</option>
                    </select>
                </div>

                <!-- Driver Container -->
                <div class="col-12 col-md-6" id="driver-container" style="display:none;">
                    <label for="driver-id" class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-person-badge me-1"></i>Select Driver
                    </label>
                </div>

                <input type="hidden" value="{{ session('user_id') }}" name="adminId">

                <!-- Location -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-geo-alt me-1"></i>Location (Street/Area) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="issue-location" name="location" class="form-control border-2 shadow-sm" placeholder="Enter street or area" required style="border-color: #f59e0b30;">
                </div>

                <!-- Date & Time -->
                <div class="col-12 col-md-6">
                    <label class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-calendar-event me-1"></i>Date & Time <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" id="issue-datetime" name="incident_datetime" class="form-control border-2 shadow-sm" required style="border-color: #f59e0b30;">
                </div>

                <!-- Priority Level -->
                <div class="col-12">
                    <label class="form-label text-uppercase small fw-bold mb-3" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-flag-fill me-1"></i>Priority Level <span class="text-danger">*</span>
                    </label>
                    <div class="btn-group w-100 shadow-sm" role="group">
                        <input type="radio" class="btn-check" name="priority" id="priority-low" value="low" required>
                        <label class="btn btn-outline-success py-2 fw-semibold" for="priority-low">
                            <i class="bi bi-check-circle-fill d-block mb-1"></i>
                            <small>Low</small>
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priority-medium" value="medium" checked>
                        <label class="btn btn-outline-warning py-2 fw-semibold" for="priority-medium">
                            <i class="bi bi-exclamation-circle-fill d-block mb-1"></i>
                            <small>Medium</small>
                        </label>

                        <input type="radio" class="btn-check" name="priority" id="priority-high" value="high">
                        <label class="btn btn-outline-danger py-2 fw-semibold" for="priority-high">
                            <i class="bi bi-x-circle-fill d-block mb-1"></i>
                            <small>High</small>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-text-paragraph me-1"></i>Description <span class="text-danger">*</span>
                    </label>
                    <textarea id="issue-description" name="description" class="form-control border-2 shadow-sm" rows="5" placeholder="Describe the issue in detail..." required style="border-color: #f59e0b30; resize: vertical;"></textarea>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>Please provide as much detail as possible
                    </small>
                </div>

                <!-- Photo Upload -->
                <div class="col-12">
                    <label class="form-label text-uppercase small fw-bold mb-2" style="color: #f59e0b; letter-spacing: 0.5px;">
                        <i class="bi bi-camera me-1"></i>Attach Photo (Optional)
                    </label>
                    <div class="position-relative">
                        <input type="file" id="issue-photo" name="photo" class="form-control border-2 shadow-sm" accept="image/*" style="border-color: #f59e0b30;">
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-image me-1"></i>Supported formats: JPG, PNG, GIF (Max 5MB)
                        </small>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn w-100 py-2 fw-bold shadow" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; color: white;">
                        <i class="bi bi-send-fill me-2"></i>Submit Report
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Decorative bottom accent -->
    <div style="height: 4px; background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #ea580c 100%);"></div>
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