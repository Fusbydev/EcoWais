@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('content')
    <div id="driver-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Driver Interface</h2>

            <div class="card">
                <h3>Today's Routes - <span id="driver-name">Driver #001</span></h3>
                <div id="driver-status" class="alert alert-success">
                    Status: On Route | Truck #5 | Next Stop: Barangay San Antonio
                </div>
                <p><strong>Logged in Driver ID:</strong> {{ session('user_id') }}</p>

                <table class="table align-middle">
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
                        $shortAddress = Str::limit($fullAddress, 40, '');
                        $isTruncated = strlen($fullAddress) > 40;
                        $rowId = $pickup->id . '-' . $index;
                    @endphp
                    
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">No pickup locations found for this truck.</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="5" class="text-center">No scheduled pickups found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
            <div class="card">
                <div class="form-group">
                    <label>Status Update</label>
                    <select id="driver-status-select">
                        <option value="on-route">On Route</option>
                        <option value="at-pickup">At Pickup Location</option>
                        <option value="break">On Break</option>
                        <option value="returning">Returning to Base</option>
                    </select>

                    <select id="pickup-location-select" style="display:none;">
                        <option disabled selected>Loading pickup locations...</option>
                    </select>
                    <button type="button" class="btn" onclick="updateDriverStatus()">Update Status</button>
                </div>
            </div>



            <div class="card">
                <div class="container mt-4">

                    <!-- DASHBOARD ROW -->
                    <div class="row mb-4">

                       <!-- Total Waste Today -->
<div class="col-md-6 mb-3">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title">Waste Collected Today</h5>
            <h2 class="fw-bold text-success">{{ $todayTotal ?? 0 }} kg</h2>
            <p class="text-muted mb-0">As of {{ now()->format('F d, Y') }}</p>
        </div>
    </div>
</div>

<!-- Total Waste This Month -->
<div class="col-md-6 mb-3">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title">Waste Collected This Month</h5>
            <h2 class="fw-bold text-primary">{{ $monthTotal ?? 0 }} kg</h2>
            <p class="text-muted mb-0">Month of {{ now()->format('F Y') }}</p>
        </div>
    </div>
</div>


                    </div>


                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3 d-flex align-items-center">
                        <i class="bi bi-recycle fs-4 me-2"></i>

                        @if (session('successWaste'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <h5 class="mb-0">Add Waste Collection Entry</h5>
                    </div>

                    <div class="card-body">

                        <form method="POST" action="{{ route('waste.store') }}">

                            @csrf

                            <div class="row g-4">
                        <input type="hidden" name="collector_id" value="{{ session('user_id') }}">

                                <!-- Location -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Location</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                        <select name="location_id" class="form-select" required>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}">{{ $loc->location }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Date -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Collection Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar-date"></i></span>
                                        <input type="date" name="waste_date" class="form-control" required>
                                    </div>
                                </div>

                                <!-- Weight -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Waste Weight (kg)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-box-seam"></i></span>
                                        <input type="number" name="weight" class="form-control" step="0.01" placeholder="Enter kilos" required>
                                    </div>
                                </div>

                                <!-- Waste Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Waste Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-tags"></i></span>
                                        <select name="waste_type" class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="Plastic">Plastic</option>
                                            <option value="Biodegradable">Biodegradable</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Glass">Glass</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <!-- Divider -->
                            <hr class="my-4">

                            <div class="text-end">
                                <button class="btn btn-primary px-4">
                                    <i class="bi bi-save2 me-1"></i> Save Entry
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
                </div>
                </div>

            <div class="card">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

                <h3>Report Issue</h3>
                <form id="driver-report-form" method="POST" action="{{ route('driver.reports.store') }}">
                    @csrf
                    <input type="hidden" name="driver_id" value="{{ session('user_id') ?? '' }}">

                    <div class="form-group">
                        <label>Issue Type</label>
                        <select id="driver-issue-type" name="issue_type" required>
                            <option value="">Select issue</option>
                            <option value="vehicle">Vehicle Problem</option>
                            <option value="access">Access Problem</option>
                            <option value="safety">Safety Concern</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group" id="other-issue-group" style="display: none;">
                        <label>Specify Other Issue</label>
                        <input type="text" id="driver-issue-other" name="other_issue" placeholder="Describe other issue">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="driver-issue-desc" name="description" required placeholder="Describe the issue"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-full">Submit Report</button>
                </form>
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