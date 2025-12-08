@extends('layouts.app')
@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div id="driver-page" class="page py-4">
    <div class="container">
        <!-- Page Header -->
        <h2 class="text-center text-white mb-4">Driver Interface</h2>

        <!-- Dashboard Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Waste Collected Today</h5>
                        <h2 class="fw-bold text-success">{{ $todayTotal ?? 0 }} kg</h2>
                        <p class="text-muted mb-0">As of {{ now()->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Waste Collected This Month</h5>
                        <h2 class="fw-bold text-primary">{{ $monthTotal ?? 0 }} kg</h2>
                        <p class="text-muted mb-0">Month of {{ now()->format('F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Routes Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-truck-front-fill me-2"></i>
                    Today's Routes - <span id="driver-name">{{ session('user_name') }}</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Progress Bar Section -->
                <div class="mb-4">
    @php
        $totalLocations = $totalCompleted + $totalPending;
        $progressPercentage = $totalLocations > 0
            ? round(($totalCompleted / $totalLocations) * 100)
            : 0;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h6 class="mb-0 fw-semibold">Route Progress</h6>
            <small class="text-muted">{{ $totalCompleted }} of {{ $totalLocations }} locations completed</small>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $progressPercentage }}%</span>
        </div>
    </div>

    <div class="progress" style="height: 30px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
             role="progressbar"
             style="width: {{ $progressPercentage }}%;"
             aria-valuenow="{{ $progressPercentage }}"
             aria-valuemin="0"
             aria-valuemax="100">
            @if($progressPercentage > 10)
                <span class="fw-semibold">{{ $progressPercentage }}% Complete</span>
            @endif
        </div>
    </div>

    @if($progressPercentage === 100)
        <div class="alert alert-success mt-3 mb-0">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>All routes completed!</strong> Great job today!
        </div>
    @elseif($progressPercentage > 0)
        <div class="alert alert-info mt-3 mb-0">
            <i class="bi bi-truck me-2"></i>
            <strong>Status:</strong> On Route | Keep up the good work!
        </div>
    @else
        <div class="alert alert-warning mt-3 mb-0">
            <i class="bi bi-clock-history me-2"></i>
            <strong>Ready to start:</strong> No locations completed yet
        </div>
    @endif
</div>


                <p class="mb-3"><strong>Logged in Driver ID:</strong> {{ session('user_id') }}</p>

                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th class="text-dark">🕒 Time</th>
                                <th class="text-dark">📍 Exact Location</th>
                                <th class="text-dark">📊 Status</th>
                                <th class="text-dark">⚙️ Action</th>
                            </tr>
                        </thead>
                        <tbody id="driver-routes">
                            @forelse($scheduledPickups as $pickup)
                                @if(!empty($pickup->converted_addresses))
                                    @foreach($pickup->converted_addresses as $index => $converted)
                                        @php
                                            $fullAddress = $converted['address'] ?? 'N/A';
                                            $shortAddress = Str::limit($fullAddress, 40, '...');
                                            $isTruncated = strlen($fullAddress) > 40;
                                            $rowId = $pickup->id . '-' . $index;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $pickup->time ?? 'N/A' }}</td>
                                            <td>
                                                <span id="short-address-{{ $rowId }}">{{ $shortAddress }}</span>
                                                @if($isTruncated)
                                                    <span id="full-address-{{ $rowId }}" class="d-none">{{ $fullAddress }}</span>
                                                    <button class="btn btn-sm btn-link p-0" onclick="toggleFullAddress('{{ $rowId }}')">More</button>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($pickup->status === 'Completed')
                                                    <span class="badge bg-success">{{ $pickup->status }}</span>
                                                @elseif($pickup->status === 'In Progress')
                                                    <span class="badge bg-warning">{{ $pickup->status }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $pickup->status ?? 'Pending' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary">Update</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">No pickup locations found for this truck.</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No scheduled pickups found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Update Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Status Update</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label>Status</label>
                        <select id="driver-status-select" class="form-select">
                            <option value="on-route">On Route</option>
                            <option value="at-pickup">At Pickup Location</option>
                            <option value="break">On Break</option>
                            <option value="returning">Returning to Base</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Pickup Location</label>
                        <select id="pickup-location-select" class="form-select" style="display:none;">
                            <option disabled selected>Loading pickup locations...</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary w-100" onclick="updateDriverStatus()">Update Status</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Waste Collection Entry -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bi bi-recycle fs-4 me-2"></i>
                <h5 class="mb-0">Add Waste Collection Entry</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('waste.store') }}">
                    @csrf
                    <input type="hidden" name="collector_id" value="{{ session('user_id') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <select name="location_id" class="form-select" required>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Collection Date</label>
                            <input type="date" name="waste_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Waste Weight (kg)</label>
                            <input type="number" name="weight" class="form-control" step="0.01" placeholder="Enter kilos" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Waste Type</label>
                            <select name="waste_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Plastic">Plastic</option>
                                <option value="Biodegradable">Biodegradable</option>
                                <option value="Metal">Metal</option>
                                <option value="Glass">Glass</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-primary px-4">
                            <i class="bi bi-save2 me-1"></i> Save Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Issue -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Report Issue</h5>
            </div>
            <div class="card-body">
                <form id="driver-report-form" method="POST" action="{{ route('driver.reports.store') }}">
                    @csrf
                    <input type="hidden" name="driver_id" value="{{ session('user_id') ?? '' }}">
                    <div class="mb-3">
                        <label class="form-label">Issue Type</label>
                        <select id="driver-issue-type" name="issue_type" class="form-select" required>
                            <option value="">Select issue</option>
                            <option value="vehicle">Vehicle Problem</option>
                            <option value="access">Access Problem</option>
                            <option value="safety">Safety Concern</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3" id="other-issue-group" style="display: none;">
                        <label class="form-label">Specify Other Issue</label>
                        <input type="text" id="driver-issue-other" name="other_issue" class="form-control" placeholder="Describe other issue">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="driver-issue-desc" name="description" class="form-control" rows="3" placeholder="Describe the issue" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const issueSelect = document.getElementById('driver-issue-type');
    const otherGroup = document.getElementById('other-issue-group');
    const otherInput = document.getElementById('driver-issue-other');

    issueSelect.addEventListener('change', () => {
        if(issueSelect.value === 'other') {
            otherGroup.style.display = 'block';
            otherInput.required = true;
        } else {
            otherGroup.style.display = 'none';
            otherInput.required = false;
        }
    });
});

function toggleFullAddress(id, collapse = false) {
    const shortEl = document.getElementById(`short-address-${id}`);
    const fullEl = document.getElementById(`full-address-${id}`);

    if (collapse) {
        shortEl.classList.remove('d-none');
        fullEl.classList.add('d-none');
    } else {
        shortEl.classList.add('d-none');
        fullEl.classList.remove('d-none');
    }
}

window.scheduledPickups = {!! json_encode($scheduledPickups) !!};
</script>
@endsection
