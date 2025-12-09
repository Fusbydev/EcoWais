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
            <div class="col-12 col-lg-12 order-1 order-lg-1">
                <!-- Barangay Information Card -->
                <div class="card shadow-lg border-0 mb-3 mb-md-4 overflow-hidden">
    <div class="card-header position-relative py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex align-items-center">
            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                <i class="bi bi-geo-alt-fill fs-4 text-white"></i>
            </div>
            <h5 class="mb-0 text-white fw-bold">Barangay Information</h5>
        </div>
    </div>
    
    <div class="card-body p-4">
        @php
            $adminLocation = $locations->firstWhere('adminId', session('user_id'));
            $totalCollectors = $collectors1->where('initial_location', $adminLocation->location)->count();
        @endphp

        <!-- Barangay Name Section -->
        <div class="mb-4 p-3 rounded-3" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
            <label class="form-label text-uppercase small fw-bold mb-2" style="color: #667eea; letter-spacing: 0.5px;">
                <i class="bi bi-pin-map-fill me-1"></i>Barangay Name
            </label>
            <h3 class="mb-0 fw-bold" style="color: #667eea;">
                {{ $adminLocation->location ?? '—' }}
            </h3>
        </div>

        <!-- Collectors Count -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border border-2" style="border-color: #667eea !important; background-color: #f8f9ff;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-people-fill fs-5" style="color: #667eea;"></i>
                    </div>
                    <span class="fw-semibold" style="color: #4a5568;">Total Collectors</span>
                </div>
                <span class="badge rounded-pill px-3 py-2 fs-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    {{ $totalCollectors }}
                </span>
            </div>
        </div>

        <!-- Assigned Drivers Section -->
        <div class="mb-4">
            <label class="form-label text-uppercase small fw-bold mb-3" style="color: #667eea; letter-spacing: 0.5px;">
                <i class="bi bi-truck me-1"></i>Assigned Drivers
            </label>
            <div class="p-3 rounded-3" style="background-color: #f8f9ff; border-left: 4px solid #667eea;">
                @if($truckData->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($truckData->pluck('name')->toArray() as $driver)
                            <span class="badge bg-light text-dark border px-3 py-2 d-flex align-items-center">
                                <i class="bi bi-person-badge-fill me-2" style="color: #667eea;"></i>
                                {{ $driver }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                        <span class="text-muted">No drivers assigned</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pickup Schedule Section -->
        <div>
            <label class="form-label text-uppercase small fw-bold mb-3" style="color: #667eea; letter-spacing: 0.5px;">
                <i class="bi bi-calendar-check me-1"></i>Pickup Schedule
            </label>
            <div class="p-3 rounded-3" style="background-color: #f8f9ff; border-left: 4px solid #10b981;">
                @if($pickupDates->count() > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($pickupDates->toArray() as $date)
                            <span class="badge px-3 py-2 d-flex align-items-center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-calendar-event me-2"></i>
                                {{ $date }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-calendar-x fs-3 text-muted d-block mb-2"></i>
                        <span class="text-muted">No scheduled pickups</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Decorative bottom accent -->
    <div style="height: 4px; background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #10b981 100%);"></div>
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