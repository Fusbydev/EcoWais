@extends('layouts.app')
    @section ('content')
    <div id="resident-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Barangay Admin Portal</h2>

            <!-- Barangay Selection -->
            <div class="card">
    <h3>📍 Barangay Information</h3>

  <div class="form-group">
    <label>Barangay</label>
    @php
        // Get the first location assigned to this admin
        $adminLocation = $locations->firstWhere('adminId', session('user_id'));
    @endphp

    @if($adminLocation)
        <h3>{{ $adminLocation->location }}</h3>
    @else
        <h3>—</h3>
    @endif
</div>

   

<div id="barangay-info" style="margin-top: 1rem;">
    <p><strong>Barangay:</strong> 
        <span id="current-barangay">
            {{ $adminLocation->location ?? '—' }}
        </span>
    </p>

@php
    // Count collectors whose initial_location matches the admin location
    $totalCollectors = $collectors1->where('initial_location', $adminLocation->location)->count();
@endphp

<p><strong>Total Assigned Collectors:</strong> {{ $totalCollectors }}</p>

<p><strong>Driver Names:</strong>
<span id="driver-names">
    @if($truckData->count() > 0)
        {{ implode(', ', $truckData->pluck('name')->toArray()) }}
    @else
        —
    @endif
</span>
</p>


  <p><strong>Pickup Dates:</strong>
    @if($pickupDates->count() > 0)
        {{ implode(', ', $pickupDates->toArray()) }}
    @else
        —
    @endif
</p>
</div>
</div>

            <!-- Driver/Collector Attendance Tracking -->
            <div class="card">
                <h3>👥 Driver/Collector Attendance</h3>
                <div class="alert alert-info" style="margin-bottom: 1rem;">
                    <strong>ℹ️ How to Mark Attendance:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
    <li>Click "Time In" to record when a driver/collector starts their shift for today’s pickup.</li>
    <li>Click "Time Out" to record when they finish their shift for the current pickup.</li>
    <li>Attendance will automatically be marked as "Present", "Late", or "Absent" based on arrival time.</li>
    <li>Standard shift: 1:00 AM - 5:00 PM. Arrivals after 7:00 AM are considered Late.</li>
</ul>

                </div>
                <div class="search-filter">
                    <!--<input type="date" id="attendance-date" value="">
                    <button class="btn btn-info" onclick="loadAttendanceData()">📅 Load Attendance</button>
                    <button class="btn btn-success" onclick="showMarkAttendance()">✓ Mark Attendance</button>
                    <button class="btn btn-warning" onclick="showQuickAttendance()">⚡ Quick Mark All</button>
                    <button class="btn btn-secondary" onclick="exportAttendance()">📊 Export</button>-->
                </div>
                
                <div class="table-responsive">
                     @php
    use Carbon\Carbon;
    $today = Carbon::now('Asia/Manila')->toDateString();
@endphp
<table class="table" id="trucks-table">
    <thead>
        <tr>
            <th>Driver/Collector Name</th>
            <th>Role</th>
            <th>Truck ID</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours Worked</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody id="trucks-tbody">
        @forelse($truckData as $truck)
            @php
                // Filter sessionPickups for today's date
                $hasTodayPickup = collect($truck['sessionPickups'])
                                    ->contains($today);
            @endphp

            @if($hasTodayPickup)
            <tr>
                <td>{{ $truck['name'] }}</td>
                <td>{{ $truck['role'] }}</td>
                <td>{{ $truck['truck_id'] }}</td>

                {{-- Time In --}}
<td>
    @if($truck['time_in'] === '-')
        <form action="{{ route('attendance.timein') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $truck['driver_user_id'] }}">
            <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
            <input type="hidden" name="session_pickup" value="{{ $today }}">
            <button type="submit" class="btn btn-sm btn-success">Time In</button>
        </form>
    @else
        {{ $truck['time_in'] }}
    @endif
</td>

                {{-- Time Out --}}
                <td>
                    @if($truck['time_out'] === '-')
                        <form action="{{ route('attendance.timeout') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $truck['driver_user_id'] }}">
                            <input type="hidden" name="location_id" value="{{ $selectedLocation->id }}">
                            <button type="submit" class="btn btn-sm btn-warning">Time Out</button>
                        </form>
                    @else
                        {{ $truck['time_out'] }}
                    @endif
                </td>

                {{-- Hours Worked --}}
                <td>{{ $truck['hours_worked'] }}</td>

                {{-- Status --}}
                <td>{{ $truck['status'] }}</td>

            </tr>
            @endif
        @empty
            <tr>
                <td colspan="8" class="text-center">Select a barangay to see assigned trucks.</td>
            </tr>
        @endforelse
    </tbody>
</table>


                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card" style="background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745;">
                    <div class="stat-number" id="present-count" style="color: #28a745;">{{ $present }}</div>
                    <div style="color: #28a745;">Present Today</div>
                </div>
                <div class="stat-card" style="background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545;">
                    <div class="stat-number" id="absent-count" style="color: #dc3545;">{{ $absent }}</div>
                    <div style="color: #dc3545;">Absent Today</div>
                </div>
                <div class="stat-card" style="background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107;">
                    <div class="stat-number" id="late-count" style="color: #856404;">{{ $late }}</div>
                    <div style="color: #856404;">Late Arrivals</div>
                </div>
            </div>

            <!-- Report an Issue -->
            <div class="card">
                <h3>📢 Report an Issue</h3>
                @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

                <form id="report-form" method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="form-group">
        <label>Issue Type</label>
        <select id="issue-type" name="issue_type" required>
            <option value="">Select issue type</option>
            <option value="missed">Missed Collection</option>
            <option value="spillage">Waste Spillage</option>
            <option value="illegal">Illegal Dumping</option>
            <option value="damaged">Damaged Bin</option>
            <option value="driver-absent">Driver/Collector Absent</option>
            <option value="vehicle">Vehicle Problem</option>
            <option value="other">Other</option>
        </select>
    </div>
<div class="form-group" id="driver-container" style="display:none;">
    <label for="driver-id">Select Driver</label>
</div>

    <div class="form-group">
        <label>Location (Street/Area)</label>
        <input type="text" id="issue-location" name="location" required>
    </div>

    <div class="form-group">
        <label>Date & Time</label>
        <input type="datetime-local" id="issue-datetime" name="incident_datetime" required>
    </div>

    <div class="form-group">
        <label>Priority Level</label>
        <select id="issue-priority" name="priority" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
    </div>

    <div class="form-group">
        <label>Description</label>
        <textarea id="issue-description" name="description" required></textarea>
    </div>

    <div class="form-group">
        <label>Attach Photo (Optional)</label>
        <input type="file" id="issue-photo" name="photo" accept="image/*">
    </div>

    <button type="submit" class="btn btn-warning btn-full">📤 Submit Report</button>
</form>

            </div>

            <!-- Submitted Reports History -->
            <div class="card">
                <h3>📋 Submitted Reports</h3>
                <div class="search-filter">
                    <select id="report-status-filter" onchange="filterReports()">
                        <option value="all">All Reports</option>
                        <option value="pending">Pending</option>
                        <option value="in-review">In Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <button class="btn btn-info" onclick="exportReports()">📊 Export Reports</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Date Submitted</th>
                <th>Issue Type</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="reports-history">
            @foreach($reports as $report)
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->incident_datetime)->format('M d, Y h:i A') }}</td>

                    <td>
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
                    <td>{{ $report->location }}</td>
                    <td>{{ ucfirst($report->priority) }}</td>
                    <td><span class="badge bg-info">Pending</span></td>
                    
                    {{-- VIEW BUTTON --}}
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewReportModal{{ $report->id }}">
                            View
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
@foreach($reports as $report)
<div class="modal fade" id="viewReportModal{{ $report->id }}" tabindex="-1" aria-labelledby="viewReportLabel{{ $report->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewReportLabel{{ $report->id }}">
                    Report Details - #{{ $report->id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    {{-- Left column: Report Info --}}
                    <div class="col-md-6">
                        <div class="card border-primary mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">Report Information</h6>
                                <p><strong>Issue Type:</strong> 
                                    {{ $report->issue_type === 'other' ? $report->other_issue : ($report->issue_type === 'driver-absent' && $report->driver && $report->driver->user ? "absent-" . $report->driver->user->name : $report->issue_type) }}
                                </p>
                                <p><strong>Location:</strong> {{ $report->location }}</p>
                                <p><strong>Priority:</strong> 
                                    @if(strtolower($report->priority) === 'high')
                                        <span class="badge bg-danger">{{ ucfirst($report->priority) }}</span>
                                    @elseif(strtolower($report->priority) === 'medium')
                                        <span class="badge bg-warning text-dark">{{ ucfirst($report->priority) }}</span>
                                    @else
                                        <span class="badge bg-success">{{ ucfirst($report->priority) }}</span>
                                    @endif
                                </p>
                                <p><strong>Status:</strong> <span class="badge bg-info">Pending</span></p>
                                <p><strong>Submitted At:</strong> {{ $report->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right column: Description & Photo --}}
                    <div class="col-md-6">
                        <div class="card border-secondary mb-3 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">Details & Image</h6>
                                <p><strong>Description:</strong> {{ $report->description }}</p>
                                @if($report->photo_path)
                                    <div class="text-center">
                                        <img src="{{ asset($report->photo_path) }}" alt="Report Image" class="img-fluid rounded shadow-sm" style="max-height:300px;">
                                    </div>
                                @else
                                    <p class="text-muted text-center">No photo submitted</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
document.addEventListener("DOMContentLoaded", function () {

    const issueType = document.getElementById("issue-type");
    const driverContainer = document.getElementById("driver-container");

    // Create driver dropdown
    const driverDropdown = document.createElement("select");
    driverDropdown.id = "driver-id";
    driverDropdown.name = "driver_id";
    driverDropdown.classList.add("form-control");
    driverContainer.appendChild(driverDropdown); // append inside container

    // Pass PHP array to JS
    const drivers = @json($drivers);

    drivers.forEach(d => {
    const opt = document.createElement("option");
    opt.value = d.user_id;
    opt.textContent = d.driver_name + (d.truck_id ? ` (Truck ID: ${d.truck_id})` : '');
    // Remove any disabling based on status
    driverDropdown.appendChild(opt);
});


    // Create "Other Issue" input
    const otherInput = document.createElement("input");
    otherInput.type = "text";
    otherInput.id = "other-issue";
    otherInput.name = "other_issue";
    otherInput.placeholder = "Specify the issue";
    otherInput.style.display = "none";
    otherInput.classList.add("form-control");
    issueType.parentNode.appendChild(otherInput);

    // Event listener
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