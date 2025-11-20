@extends('layouts.app')
    @section ('content')
    <div id="resident-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Barangay Admin Portal</h2>

            <!-- Barangay Selection -->
            <div class="card">
    <h3>📍 Barangay Information</h3>

    <div class="form-group">
    <label>Select Your Barangay</label>
    <select class="form-select" id="barangay">
        <option value="" disabled selected>Select a barangay</option>
        @foreach ($locations as $location)
            <option 
                value="{{ $location->id }}"  {{-- ID for reference, not used --}}
                data-name="{{ $location->location }}">
                {{ $location->location }}
            </option>
        @endforeach
    </select>
</div>


    <div id="barangay-info" style="margin-top: 1rem;">
        <p><strong>Barangay:</strong> 
            <span id="current-barangay">
                {{ $selectedLocation->location ?? '—' }}
            </span>
        </p>

        <p><strong>Assigned Collectors:</strong> 
            <span id="assigned-collectors">
                {{ $collectors->count() ?? '—' }}
            </span>
        </p>

        <p><strong>Collector Names:</strong> 
            <span id="collector-names">
                @if(isset($collectors) && $collectors->count() > 0)
                    {{ implode(', ', $collectors->pluck('user.name')->toArray()) }}
                @else
                    —
                @endif
            </span>
        </p>

        <p><strong>Collection Days:</strong> 
            <span id="collection-days">
                {{ $selectedLocation->collection_days ?? '—' }}
            </span>
        </p>
    </div>
</div>


            <!-- Driver/Collector Attendance Tracking -->
            <div class="card">
                <h3>👥 Driver/Collector Attendance</h3>
                <div class="alert alert-info" style="margin-bottom: 1rem;">
                    <strong>ℹ️ How to Mark Attendance:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <li>Click "✓ Mark Attendance" to record when a driver/collector arrives</li>
                        <li>Click "⏰ Time Out" button to record when they finish their shift</li>
                        <li>Mark as "Present", "Late", or "Absent" based on their arrival time</li>
                        <li>Standard shift: 1:00 AM - 5:00 PM (Late if after 7:00 AM)</li>
                    </ul>
                </div>
                <div class="search-filter">
                    <input type="date" id="attendance-date" value="">
                    <button class="btn btn-info" onclick="loadAttendanceData()">📅 Load Attendance</button>
                    <button class="btn btn-success" onclick="showMarkAttendance()">✓ Mark Attendance</button>
                    <button class="btn btn-warning" onclick="showQuickAttendance()">⚡ Quick Mark All</button>
                    <button class="btn btn-secondary" onclick="exportAttendance()">📊 Export</button>
                </div>
                
                <div class="table-responsive">
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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="trucks-tbody">
        <tr>
            <td colspan="8" class="text-center">Select a barangay to see assigned trucks.</td>
        </tr>
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
                <form id="report-form">
                    <div class="form-group">
                        <label>Issue Type</label>
                        <select id="issue-type" required>
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
                    <div class="form-group">
                        <label>Location (Street/Area in Barangay)</label>
                        <input type="text" id="issue-location" required placeholder="e.g., Purok 1, near Municipal Hall">
                    </div>
                    <div class="form-group">
                        <label>Date & Time of Incident</label>
                        <input type="datetime-local" id="issue-datetime" required>
                    </div>
                    <div class="form-group">
                        <label>Priority Level</label>
                        <select id="issue-priority" required>
                            <option value="low">Low - Can wait a few days</option>
                            <option value="medium">Medium - Need attention soon</option>
                            <option value="high">High - Urgent attention needed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="issue-description" required placeholder="Describe the issue in detail"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Attach Photo (Optional)</label>
                        <input type="file" id="issue-photo" accept="image/*">
                        <small style="color: #666; display: block; margin-top: 0.5rem;">Supported formats: JPG, PNG (Max 5MB)</small>
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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="trucks-tbody">
        <tr>
            <td colspan="8" class="text-center">Select a barangay to load trucks.</td>
        </tr>
    </tbody>
</table>

                </div>
            </div>
        </div>
    </div>

    
<script>
document.getElementById('barangay').addEventListener('change', function () {
    let selectedOption = this.options[this.selectedIndex];

// Get the data-name attribute
    let barangayName = selectedOption.getAttribute('data-name');
    console.log("Selected barangay:", barangayName);
    let barangayId = this.value; // ID for table API

    document.getElementById('current-barangay').textContent = barangayName;

    fetch(`/barangay/${encodeURIComponent(barangayName)}/collectors`)
        .then(res => res.json())
        .then(data => {
            console.log("Full response:", data);

            // count trucks
            document.getElementById('assigned-collectors').textContent = data.length;

            // names
            let names = data.map(c => c.driver_name).join(", ");
            document.getElementById('collector-names').textContent = names || "None";
        })
        .catch(err => console.error("Error fetching collectors:", err));


        fetch(`/barangay/${barangayId}/trucks`)
        .then(res => res.json())
        .then(trucks => {
            const tbody = document.getElementById('trucks-tbody');
            tbody.innerHTML = '';
            
            console.log(trucks || "No trucks assigned to this barangay.");
            if (!trucks.length) {
                tbody.innerHTML = `<tr>
                    <td colspan="8" class="text-center">No trucks assigned to this barangay.</td>
                </tr>`;
                return;
            }
trucks.forEach(truck => {
    console.log('Truck data:', truck);
    
    // Check if time_in exists
    let timeInCell = '';
    if (!truck.time_in || truck.time_in === null) {
        timeInCell = `
            <form method="POST" action="/attendance/time-in" onsubmit="return confirm('Record time in for ${truck.driver_name}?');">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                <input type="hidden" name="user_id" value="${truck.user_id}">
                <input type="hidden" name="location_id" value="${barangayId}">
                <button type="submit" class="btn btn-success btn-sm">✓ Time In</button>
            </form>
        `;
    } else {
        timeInCell = `<span class="text-success">${new Date(truck.time_in).toLocaleTimeString()}</span>`;
    }

    // Check if time_out exists
    let timeOutCell = '';
    if (!truck.time_out || truck.time_out === null) {
        if (truck.time_in && truck.time_in !== null) {
            timeOutCell = `
                <form method="POST" action="/attendance/time-out" onsubmit="return confirm('Record time out for ${truck.driver_name}?');">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                    <input type="hidden" name="user_id" value="${truck.user_id}">
                    <input type="hidden" name="location_id" value="${barangayId}">
                    <button type="submit" class="btn btn-warning btn-sm">⏰ Time Out</button>
                </form>
            `;
        } else {
            timeOutCell = `<span class="text-muted">—</span>`;
        }
    } else {
        timeOutCell = `<span class="text-warning">${new Date(truck.time_out).toLocaleTimeString()}</span>`;
    }

    // Calculate hours worked
    let hoursWorked = '—';
    if (truck.time_in && truck.time_out) {
        const timeIn = new Date(truck.time_in);
        const timeOut = new Date(truck.time_out);
        const diffMs = timeOut - timeIn; // Difference in milliseconds
        const diffHours = diffMs / (1000 * 60 * 60); // Convert to hours
        
        // Format as hours and minutes
        const hours = Math.floor(diffHours);
        const minutes = Math.round((diffHours - hours) * 60);
        
        hoursWorked = `${hours}h ${minutes}m`;
    }

    // Determine status
    let statusCell = truck.status || 'Absent';
    let statusClass = truck.status === 'Present' ? 'text-success' : 
                      truck.status === 'Late' ? 'text-warning' : 'text-danger';
    
    tbody.innerHTML += `
    <tr>
        <td>${truck.driver_name}</td>
        <td>${truck.role}</td>
        <td>${truck.truck_id}</td>
        <td>${timeInCell}</td>
        <td>${timeOutCell}</td>
        <td><strong>${hoursWorked}</strong></td>
        <td><span class="${statusClass}">${statusCell}</span></td>
        <td>
            <a href="#" class="btn btn-primary btn-sm">View</a>
            <a href="#" class="btn btn-warning btn-sm">Edit</a>
        </td>
    </tr>`;
});




        })
        .catch(err => console.error("Error fetching trucks:", err));
});

document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('ajax-attendance-form')) {
        e.preventDefault(); // prevent page reload

        const form = e.target;
        const action = form.action;
        const formData = new FormData(form);

        fetch(action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.text()) // controller returns redirect, ignore
        .then(() => {
            // Disable the button and mark as done
            const button = form.querySelector('button');
            button.disabled = true;
            button.textContent += ' ✅';
        })
        .catch(err => console.error(err));
    }
});

</script>

@endsection