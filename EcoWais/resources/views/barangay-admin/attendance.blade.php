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
<!-- Attendance Summary -->
                <div class="row g-2 g-md-3 attendance-summary d-flex">
                    <div class="col-4">
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
                    <div class="col-4">
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
                    <div class="col-4">
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
        
            <!-- Right Column -->
            <div class="col-12 col-lg-12 order-2 order-lg-2">
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
                                            $hasTodayPickup = ($truck['pickup_date'] <= $today);
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
                                                        <input type="hidden" name="session_pickup" value="{{ $truck['pickup_date'] }}">
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
                                                        <input type="hidden" name="session_pickup" value="{{ $truck['pickup_date'] }}">
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
                                                No Pickups Today
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
</div>

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