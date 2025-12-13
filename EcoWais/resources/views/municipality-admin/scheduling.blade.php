@extends('layouts.app')

@section('content')
<div id="admin-barangay-page" class="page">
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-white mb-2">
                <i class="bi bi-calendar-check-fill me-2"></i>Barangay Waste Pickup Scheduling
            </h2>
            <p class="text-white-50">Schedule and manage waste collection pickups for barangays</p>
        </div>

             <!-- Statistics Cards -->
        @if($pickups->count() > 0)
            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Scheduled</h6>
                                    <h2 class="fw-bold text-success mb-0">{{ $pickups->count() }}</h2>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-calendar-check-fill text-success fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Barangays Covered</h6>
                                    <h2 class="fw-bold text-primary mb-0">{{ $pickups->unique('location_id')->count() }}</h2>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-geo-alt-fill text-primary fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Trucks Assigned</h6>
                                    <h2 class="fw-bold text-info mb-0">{{ $pickups->whereNotNull('truck_id')->unique('truck_id')->count() }}</h2>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-truck-front-fill text-info fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
        <!-- Success Alert -->
        @if (session('pickupSuccess'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('pickupSuccess') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Schedule Pickup Form -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle-fill me-2"></i>Schedule New Pickup
                        </h5>
                    </div>
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="card-body p-4">
                        <form action="{{ route('pickup.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                                    Select Barangay <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="initial_location" name="initial_location" required>
                                    <option value="">— Choose a barangay —</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->location }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-event text-success me-1"></i>
                                    Pickup Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       id="admin-pickup-date" 
                                       name="admin-pickup-date" 
                                       class="form-control form-control-lg" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock-fill text-warning me-1"></i>
                                    Pickup Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       id="admin-pickup-time" 
                                       name="admin-pickup-time" 
                                       class="form-control form-control-lg" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-truck-front-fill text-info me-1"></i>
                                    Assigned Truck <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="truck" name="truck" required>
                                    <option value="">— Select a truck —</option>
                                    @foreach($trucks as $truck)
                                        <option value="{{ $truck->id }}">
                                            {{ $truck->truck_id }}
                                            @if($truck->driver && $truck->driver->user)
                                                - {{ $truck->driver->user->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i>Schedule Pickup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Scheduled Pickups Table -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-check me-2"></i>Scheduled Pickups
                            </h5>
                            <span class="badge bg-primary rounded-pill">
                                {{ $pickups->count() }} Total
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">
                                            <i class="bi bi-geo-alt-fill me-1"></i>Barangay
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-calendar-event me-1"></i>Date
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-clock-fill me-1"></i>Time
                                        </th>
                                        <th class="fw-semibold">
                                            <i class="bi bi-truck-front-fill me-1"></i>Truck
                                        </th>
                                        <th class="fw-semibold text-center">
                                            <i class="bi bi-gear-fill me-1"></i>Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="admin-barangay-schedule-table">
                                    @forelse ($pickups as $pickup)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                                    </div>
                                                    <span class="fw-medium">{{ $pickup->location->location ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('M d, Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ \Carbon\Carbon::parse($pickup->pickup_time)->format('h:i A') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($pickup->truck)
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-truck-front-fill text-info me-2"></i>
                                                        <span>{{ $pickup->truck->truck_id }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="bi bi-dash-circle me-1"></i>Unassigned
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('pickup.destroy', $pickup->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')

                                                @if($pickup->Archived === 'true')
                                                    <button class="btn btn-success btn-sm">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                    </button>
                                                @else
                                                    <button class="btn btn-warning btn-sm">
                                                        <i class="bi bi-archive"></i> Archive
                                                    </button>
                                                @endif
                                            </form>


                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                    <p class="mb-0">No scheduled pickups yet</p>
                                                    <small>Create your first pickup schedule using the form</small>
                                                </div>
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

   

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dateInput = document.getElementById('admin-pickup-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }

    // Add form validation feedback
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        // Remove invalid class on input
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    }
});
</script>

@endsection