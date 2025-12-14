@extends('layouts.app')
@section('content')

<style>
    /* Mobile-first responsive enhancements */
    @media (max-width: 767.98px) {
        .container-fluid {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .row.g-4 {
            gap: 1rem !important;
        }

        .text-center h2 {
            font-size: 1.5rem;
        }

        .text-center p {
            font-size: 0.875rem;
        }

        .card {
            margin-bottom: 1rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-group {
            flex-direction: column;
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            font-size: 0.875rem;
        }

        .attendance-summary .col-12 {
            margin-bottom: 0.75rem;
        }

        .modal-xl {
            max-width: 95%;
        }

        .card-body h2 {
            font-size: 1.75rem;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }

    @media (min-width: 768px) and (max-width: 991.98px) {
        .container-fluid {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }

        .attendance-summary .col-12 {
            flex: 0 0 auto;
            width: 50%;
        }
    }

    .card-header h5 {
        font-size: clamp(1rem, 2.5vw, 1.25rem);
    }

    @media (max-width: 575.98px) {
        .table td, .table th {
            padding: 0.5rem 0.25rem;
        }

        .col-md-6, .col-md-12 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }

    /* Enhanced minimal styles */
    .stat-card {
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    }

    .driver-badge {
        transition: all 0.2s ease;
    }

    .driver-badge:hover {
        background-color: #e2e8f0 !important;
        transform: translateX(2px);
    }

    .schedule-badge {
        transition: all 0.2s ease;
    }

    .schedule-badge:hover {
        transform: scale(1.05);
    }
</style>


    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="text-center mb-4 mb-md-5 py-4 px-3 rounded-3" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);">
            <h2 class="fw-bold text-white mb-2">Barangay Admin Portal</h2>
            <p class="text-white mb-0" style="opacity: 0.85;">Manage attendance, track reports, and monitor barangay operations</p>
        </div>
        
        <div class="row g-3 g-md-4">
            <div class="col-12 col-lg-12 order-1 order-lg-1">
                <!-- Enhanced Barangay Information Card -->
                <div class="card border-0 mb-3 mb-md-4" style="box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);">
                    <div class="card-header bg-white border-bottom py-4" style="border-bottom-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-2 p-2 me-3" style="background-color: #f1f5f9;">
                                <i class="bi bi-geo-alt-fill fs-5" style="color: #475569;"></i>
                            </div>
                            <h5 class="mb-0 fw-semibold" style="color: #1e293b;">Barangay Information</h5>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        @php
                            $adminLocation = $locations->firstWhere('adminId', session('user_id'));
                            $totalCollectors = $collectors1->where('initial_location', $adminLocation->location)->count();
                        @endphp

                        <!-- Barangay Name Section -->
                        <div class="mb-4 pb-4 border-bottom">
                            <label class="form-label text-uppercase small fw-medium mb-3" style="color: #64748b; letter-spacing: 0.05em; font-size: 0.75rem;">
                                Barangay Name
                            </label>
                            <h2 class="mb-0 fw-bold" style="color: #0f172a; font-size: 1.875rem; line-height: 1.2;">
                                {{ $adminLocation->location ?? '—' }}
                            </h2>
                        </div>

                        <!-- Stats Row -->
                        <div class="row g-4 mb-4">
                            <!-- Collectors Count -->
                            <div class="col-12 col-md-6">
                                <div class="stat-card d-flex align-items-start p-3 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="rounded-2 p-2 me-3" style="background-color: #dbeafe;">
                                        <i class="bi bi-people-fill fs-5" style="color: #2563eb;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase small fw-medium mb-1" style="color: #64748b; font-size: 0.75rem; letter-spacing: 0.05em;">
                                            Total Collectors
                                        </div>
                                        <div class="fs-3 fw-bold" style="color: #0f172a;">
                                            {{ $totalCollectors }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drivers Count -->
                            <div class="col-12 col-md-6">
                                <div class="stat-card d-flex align-items-start p-3 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="rounded-2 p-2 me-3" style="background-color: #dcfce7;">
                                        <i class="bi bi-truck fs-5" style="color: #16a34a;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-uppercase small fw-medium mb-1" style="color: #64748b; font-size: 0.75rem; letter-spacing: 0.05em;">
                                            Assigned Drivers
                                        </div>
                                        <div class="fs-3 fw-bold" style="color: #0f172a;">
                                            {{ $truckData->count() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assigned Drivers Section -->
                        <div class="mb-4">
                            <label class="form-label text-uppercase small fw-medium mb-3" style="color: #64748b; letter-spacing: 0.05em; font-size: 0.75rem;">
                                Driver List
                            </label>
                            @if($truckData->count() > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($truckData->pluck('name')->toArray() as $driver)
                                        <span class="driver-badge badge rounded-2 px-3 py-2" style="background-color: #f1f5f9; color: #334155; font-weight: 500; font-size: 0.875rem; border: 1px solid #e2e8f0;">
                                            <i class="bi bi-person-badge me-2" style="color: #64748b;"></i>
                                            {{ $driver }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                    <i class="bi bi-inbox fs-2 d-block mb-2" style="color: #cbd5e1;"></i>
                                    <span style="color: #64748b; font-size: 0.875rem;">No drivers assigned</span>
                                </div>
                            @endif
                        </div>

                        <!-- Pickup Schedule Section -->
<div>
    <label class="form-label text-uppercase small fw-medium mb-3" style="color: #64748b; letter-spacing: 0.05em; font-size: 0.75rem;">
        Pickup Schedule
    </label>
    @php
        $today = \Carbon\Carbon::today();
        
        // Get all upcoming pickups with their times
        $upcomingPickups = \App\Models\Pickup::where('location_id', $selectedLocation->id)
            ->where('pickup_date', '>=', $today)
            ->orderBy('pickup_date', 'asc')
            ->orderBy('pickup_time', 'asc')
            ->get();
        
        // Group by date
        $groupedByDate = $upcomingPickups->groupBy('pickup_date');
    @endphp
    
    @if($groupedByDate->count() > 0)
        <div class="d-flex flex-wrap gap-2">
            @foreach($groupedByDate as $date => $pickups)
                <div class="schedule-item">
                    <button type="button" 
                            class="schedule-badge badge rounded-2 px-3 py-2 d-flex align-items-center border-0" 
                            style="background-color: #dcfce7; color: #166534; font-weight: 500; font-size: 0.875rem; border: 1px solid #bbf7d0; cursor: pointer;"
                            @if($pickups->count() > 1)
                                data-bs-toggle="modal" 
                                data-bs-target="#pickupTimesModal-{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
                            @endif>
                        <i class="bi bi-calendar-event me-2"></i>
                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                        @if($pickups->count() > 1)
                            <span class="ms-2 badge rounded-pill" style="background-color: #16a34a; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                {{ $pickups->count() }}x
                            </span>
                        @else
                            <span class="ms-2" style="color: #166534; font-size: 0.75rem;">
                                {{ \Carbon\Carbon::parse($pickups->first()->pickup_time)->format('g:i A') }}
                            </span>
                        @endif
                    </button>
                    
                    
                </div>
            @endforeach
        </div>

        <p class="text-muted small mt-2" style="color: #64748b;">To see the time of each pickup, click on the badge.</p>
    @else
        <div class="text-center py-4 rounded-2" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
            <i class="bi bi-calendar-x fs-2 d-block mb-2" style="color: #cbd5e1;"></i>
            <span style="color: #64748b; font-size: 0.875rem;">No scheduled pickups</span>
        </div>
    @endif
</div>
                    </div>
                </div>
@if(!empty($pickups) && $pickups->count() > 1)
                        <!-- Modal for showing times -->
                        <div class="modal fade" id="pickupTimesModal-{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                    <div class="modal-header bg-white border-bottom" style="border-bottom-color: #e2e8f0 !important;">
                                        <h5 class="modal-title fw-semibold" style="color: #1e293b;">
                                            <i class="bi bi-clock me-2" style="color: #16a34a;"></i>
                                            Pickup Times
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <small class="text-uppercase fw-medium" style="color: #64748b; font-size: 0.75rem; letter-spacing: 0.05em;">
                                                Date
                                            </small>
                                            <div class="fw-semibold mt-1" style="color: #0f172a; font-size: 1.125rem;">
                                                {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                            </div>
                                        </div>
                                        
                                        <hr style="border-color: #e2e8f0;">
                                        
                                        <div>
                                            <small class="text-uppercase fw-medium mb-3 d-block" style="color: #64748b; font-size: 0.75rem; letter-spacing: 0.05em;">
                                                Scheduled Times ({{ $pickups->count() }} pickups)
                                            </small>
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($pickups as $index => $pickup)
                                                    <div class="p-3 rounded-2 d-flex align-items-center justify-content-between" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                                        <div class="d-flex align-items-center">
                                                            <div class="rounded-circle p-2 me-3" style="background-color: #dcfce7;">
                                                                <i class="bi bi-clock-fill" style="color: #16a34a; font-size: 0.875rem;"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold" style="color: #0f172a;">
                                                                    {{ \Carbon\Carbon::parse($pickup->pickup_time)->format('g:i A') }}
                                                                </div>
                                                                <small style="color: #64748b;">Pickup #{{ $index + 1 }}</small>
                                                            </div>
                                                        </div>
                                                        <span class="badge rounded-2 px-2 py-1" style="background-color: #dcfce7; color: #166534; font-weight: 500; font-size: 0.75rem;">
                                                            Active
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-white border-top" style="border-top-color: #e2e8f0 !important;">
                                        <button type="button" class="btn btn-sm px-4" data-bs-dismiss="modal" style="background-color: #f1f5f9; color: #475569; border: none;">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                <!-- Driver Pickup Schedule Table -->
                <div class="card border-0 mb-3 mb-md-4" style="box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);">
                    <div class="card-header bg-white border-bottom py-4" style="border-bottom-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-2 p-2 me-3" style="background-color: #f1f5f9;">
                                    <i class="bi bi-calendar-check fs-5" style="color: #475569;"></i>
                                </div>
                                <h5 class="mb-0 fw-semibold" style="color: #1e293b;">Driver Pickup Schedule</h5>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        @if($truckData->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead style="background-color: #f8fafc;">
                                        <tr>
                                            <th class="fw-semibold py-3 px-4" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Driver Name</th>
                                            <th class="fw-semibold py-3 px-4" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Pickup Schedule</th>
                                            <th class="fw-semibold py-3 px-4" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Pickup</th>
                                            <th class="fw-semibold py-3 px-4" style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($truckData as $truck)
                                            @php
                                                // Get pickup dates for this driver's truck
                                                $driverPickups = \App\Models\Pickup::where('truck_id', $truck['truck_id'])
                                                    ->where('pickup_date', '>=', \Carbon\Carbon::today())
                                                    ->orderBy('pickup_date', 'asc')
                                                    ->get();
                                                
                                                $status = $truck['status'] ?? 'Not Recorded';
                                                $statusColor = match($status) {
                                                    'on-route' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#bfdbfe', 'label' => '🛣️ On Route'],
                                                    'break' => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a', 'label' => '☕ On Break'],
                                                    'returning' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0', 'label' => '🏠 Returning to Base'],
                                                    default => ['bg' => '#f1f5f9', 'text' => '#64748b', 'border' => '#e2e8f0', 'label' => 'Not Recorded']
                                                };
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-2 p-2 me-3" style="background-color: #dbeafe;">
                                                            <i class="bi bi-person-fill" style="color: #2563eb;"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold" style="color: #0f172a;">{{ $truck['name'] }}</div>
                                                            <div class="small" style="color: #64748b;">Truck ID: {{ $truck['truck_id'] ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($driverPickups->count() > 0)
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($driverPickups->take(3) as $pickup)
                                                                <span class="badge rounded-2 px-2 py-1" style="background-color: #dbeafe; color: #1e40af; font-weight: 500; font-size: 0.75rem; border: 1px solid #bfdbfe;">
                                                                    <i class="bi bi-calendar-event me-1"></i>
                                                                    {{ \Carbon\Carbon::parse($pickup->pickup_date)->format('M d, Y') }}
                                                                </span>
                                                            @endforeach
                                                            @if($driverPickups->count() > 3)
                                                                <span class="badge rounded-2 px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-weight: 500; font-size: 0.75rem;">
                                                                    +{{ $driverPickups->count() - 3 }} more
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="small" style="color: #94a3b8;">No upcoming pickups</span>
                                                    @endif
                                                </td>

                                                <td class="px-4 py-3">
    @php
        $truckPickups = collect($truck['pickups'] ?? []);
    @endphp

    @if($truckPickups->count() > 0)
        <div class="d-flex flex-wrap gap-2">
            @foreach($truckPickups->take(3) as $pickup)
                <span class="pickup-badge badge rounded-2 px-2 py-1" 
                      data-lat="{{ $pickup['lat'] }}" 
                      data-lng="{{ $pickup['lng'] }}"
                      style="background-color: #dbeafe; color: #1e40af; font-weight: 500; font-size: 0.75rem; border: 1px solid #bfdbfe;">
                    <i class="bi bi-geo-alt me-1"></i>
                    Loading...
                </span>
            @endforeach
            @if($truckPickups->count() > 3)
                <span class="badge rounded-2 px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-weight: 500; font-size: 0.75rem;">
                    +{{ $truckPickups->count() - 3 }} more
                </span>
            @endif
        </div>
    @else
        <span class="small" style="color: #94a3b8;">No pickups/routes</span>
    @endif
</td>


                                                <td class="px-4 py-3">
                                                    <span class="badge rounded-2 px-3 py-2" style="background-color: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }}; font-weight: 500; font-size: 0.875rem; border: 1px solid {{ $statusColor['border'] }};">
                                                        {{ $statusColor['label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5" style="background-color: #f8fafc;">
                                <i class="bi bi-inbox fs-1 d-block mb-3" style="color: #cbd5e1;"></i>
                                <h6 class="fw-semibold mb-2" style="color: #64748b;">No Drivers Available</h6>
                                <p class="small mb-0" style="color: #94a3b8;">There are no drivers assigned to this barangay yet.</p>
                            </div>
                        @endif
                    </div>
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


document.addEventListener('DOMContentLoaded', function () {

    async function reverseGeocode(lat, lng) {
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                headers: {
                    'Accept-Language': 'en',
                    'User-Agent': 'MyApp/1.0' // Nominatim requires a user-agent
                }
            });

            if (!res.ok) throw new Error('Failed to fetch');

            const data = await res.json();
            // Use display_name or parts of address if you want
            return data.display_name || 'Unknown location';
        } catch (err) {
            console.error(err);
            return 'Unknown location';
        }
    }

    document.querySelectorAll('.pickup-badge').forEach(async function(badge) {
        const lat = parseFloat(badge.dataset.lat);
        const lng = parseFloat(badge.dataset.lng);

        if (!isNaN(lat) && !isNaN(lng)) {
            const address = await reverseGeocode(lat, lng);
            badge.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${address}`;
        } else {
            badge.innerHTML = `<i class="bi bi-geo-alt me-1"></i>Invalid coords`;
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    const select = document.getElementById('issue-type');

    if (select) {
        fetch('{{ route("issues.get") }}')
            .then(response => response.json())
            .then(data => {
                select.innerHTML = '<option value="">Select issue type</option>';
                
                data.forEach(issue => {
                    const option = document.createElement('option');
                    option.value = issue.issue_name;
                    option.text = issue.issue_name;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching issues:', error));
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const issueType = document.getElementById("issue-type");
    const driverContainer = document.getElementById("driver-container");

    if (issueType && driverContainer) {
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
    }
});
</script>

@endsection