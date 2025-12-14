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
                        <h2 class="fw-bold text-success">{{ $todayTotal}} kg</h2>
                        <p class="text-muted mb-0">As of {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y') }}</p>

                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Waste Collected This Month</h5>
                        <h2 class="fw-bold text-primary">{{ $monthTotal}} kg</h2>
                        <p class="text-muted mb-0">Month of {{ now()->format('F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Routes Card -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-truck-front-fill me-2"></i>
            Today's Routes - <span id="driver-name">{{ session('user_name') }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($todayPickups->isEmpty())
            <div class="alert alert-warning mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>No pickups scheduled for today.</strong>
            </div>
        @else
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
        @endif
    </div>



                
                    <p class="mb-3"><strong>Logged in Driver ID:</strong> {{ session('user_id') }}</p>


                <div class="table-responsive">
                    <div class="mb-3 d-flex justify-content-end">
                        <div class="input-group" style="max-width: 260px;">
                            <span class="input-group-text">Filter Date</span>
                            <input type="date" id="filter-date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <table class="table align-middle table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th class="text-dark">🕒 Date</th>
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
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('F d, Y') }}
                                            </td>

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

<div class="card mb-4 border-0" style="box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);">
    <div class="card-header bg-white border-bottom py-4" style="border-bottom-color: #e2e8f0 !important;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="rounded-2 p-2 me-3" style="background-color: #f1f5f9;">
                    <i class="bi bi-info-circle fs-5" style="color: #475569;"></i>
                </div>
                <h5 class="mb-0 fw-semibold" style="color: #1e293b;">Status Update</h5>
            </div>
            <span class="badge rounded-2 px-3 py-2" style="background-color: #f1f5f9; color: #64748b; font-weight: 500; font-size: 0.875rem;">
                Driver Dashboard
            </span>
        </div>
    </div>
    
    <div class="card-body p-4">
        <form action="{{ route('driver.update.status') }}" method="POST">
            @csrf
            
            @if(session('statusSuccess'))
                <div class="alert alert-dismissible fade show mb-4" role="alert" style="background-color: #dcfce7; border: 1px solid #bbf7d0; color: #166534;">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('statusSuccess') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('statusError'))
                <div class="alert alert-dismissible fade show mb-4" role="alert" style="background-color: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('statusError') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="row g-4">
                <div class="col-12 col-md-7">
                    <label for="driver-status-select" class="form-label text-uppercase small fw-medium mb-3" style="color: #64748b; letter-spacing: 0.05em; font-size: 0.75rem;">
                        Select Current Status
                    </label>
                    <select id="driver-status-select" name="status" class="form-select py-3" required style="border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                        <option value="on-route">🛣️ On Route</option>
                        <option value="break">☕ On Break</option>
                        <option value="returning">🏠 Returning to Base</option>
                    </select>
                </div>
                
                <div class="col-12 col-md-5 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 py-3" style="background-color: #2563eb; border: none; border-radius: 0.5rem; font-weight: 500;">
                        <i class="bi bi-arrow-clockwise me-2"></i>Update Status
                    </button>
                </div>
            </div>
            
            <div class="mt-4 p-3 rounded-2" style="background-color: #f8fafc; border-left: 3px solid #2563eb;">
                <small style="color: #64748b;">
                    <i class="bi bi-info-circle me-2" style="color: #2563eb;"></i>
                    Select your current status and click <strong style="color: #1e293b;">Update Status</strong> to notify the system of your location.
                </small>
            </div>
        </form>
    </div>
</div>
</div>


        <!-- Add Waste Collection Entry -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bi bi-recycle fs-4 me-2"></i>
                <h5 class="mb-0">Add Waste Collection Entry</h5>
            </div>
            @if(session('errorWaste'))
    <div class="alert alert-danger">
        {{ session('errorWaste') }}
    </div>
@endif

@if(session('successWaste'))
    <div class="alert alert-success">
        {{ session('successWaste') }}
    </div>
@endif

            <div class="card-body">
                <form method="POST" action="{{ route('waste.store') }}">
    @csrf
    <input type="hidden" name="collector_id" value="{{ session('user_id') }}">
    <input type="hidden" name="truck_id" value="{{ $truckId }}">

    <div class="row g-3">
        <div class="col-md-6">
    <label class="form-label fw-semibold">Location</label>
    <select name="location_id" class="form-select" required>
        @php
            // Find the location assigned to this truck
            $truckLocation = $locations->firstWhere('location', $truckInitialLocation);
        @endphp
        @if($truckLocation)
            <option value="{{ $truckLocation->id }}">{{ $truckLocation->location }}</option>
        @endif
    </select>
</div>


        @php
    $todayManila = \Carbon\Carbon::now('Asia/Manila')->toDateString();
@endphp

<div class="col-md-6">
    <label class="form-label fw-semibold">Collection Date</label>
    <input type="date" name="waste_date" class="form-control" required max="{{ $todayManila }}">
</div>


        <div class="col-md-6">
            <label class="form-label fw-semibold">Waste Weight (kg)</label>
            <input type="number" name="weight" class="form-control" step="0.01" placeholder="Enter kilos" required>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold">Waste Type</label>
            <select id="waste_type_select" class="form-select" required>
                <option value="">Select Type</option>
                <option value="Plastic">Plastic</option>
                <option value="Biodegradable">Biodegradable</option>
                <option value="Metal">Metal</option>
                <option value="Glass">Glass</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="col-md-6" id="other_waste_type_container" style="display: none;">
            <label class="form-label fw-semibold">Specify Waste Type</label>
            <input type="text" id="other_waste_type" class="form-control" placeholder="Enter waste type">
        </div>

        <!-- Hidden input that will always contain the final waste type -->
        <input type="hidden" name="waste_type" id="waste_type_hidden">
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

    document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('waste_type_select');
    const otherContainer = document.getElementById('other_waste_type_container');
    const otherInput = document.getElementById('other_waste_type');
    const hiddenInput = document.getElementById('waste_type_hidden');

    function updateWasteType() {
        if (select.value === 'Other') {
            otherContainer.style.display = 'block';
            hiddenInput.value = otherInput.value; // fallback if user types something
        } else {
            otherContainer.style.display = 'none';
            hiddenInput.value = select.value;
        }
    }

    select.addEventListener('change', updateWasteType);
    otherInput.addEventListener('input', function() {
        hiddenInput.value = otherInput.value;
    });

    // Initialize on page load
    updateWasteType();
});
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
