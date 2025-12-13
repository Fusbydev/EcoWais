@extends('layouts.app')

@section('content')

<div id="tracking-page" class="page">
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="text-center mb-4">
            <h2 class="fw-bold text-white mb-2">
                <i class="bi bi-truck-front-fill me-2"></i>Truck Monitoring System
            </h2>
            <p class="text-white-50">Real-time GPS tracking with OpenStreetMap integration</p>
        </div>

        <!-- Tracking Controls -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="bi bi-funnel-fill me-1"></i>FILTER TRUCKS
                        </label>
                        <select id="truck-filter" class="form-select">
                            <option value="all">All Trucks</option>
                            <option value="active">Active Only</option>
                            <option value="idle">Idle Trucks</option>
                            <option value="maintenance">Under Maintenance</option>
                        </select>
                    </div>
                    
                
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">
                            <i class="bi bi-clock-fill me-1"></i>UPDATE INTERVAL
                        </label>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="update-interval" class="form-select" style="width: 150px;">
                                <option value="5000">5 seconds</option>
                                <option value="10000">10 seconds</option>
                                <option value="30000">30 seconds</option>
                                <option value="60000">1 minute</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Container with Enhanced Features -->
        <div class="card shadow-sm border-0 mb-4 position-relative">
            <div class="card-body p-0">
                <div id="tracking-map" style="height: 600px; border-radius: 0.375rem;"></div>
                <div id="street-view" style="width: 100%; height: 600px; display: none; border-radius: 0.375rem;"></div>
                
                <!-- Map Legend -->
                <div class="position-absolute top-0 end-0 m-3" style="z-index: 1000;">
                    <div class="card shadow-sm border-0" style="width: 200px;">
                        <div class="card-header bg-dark text-white py-2">
                            <h6 class="mb-0"><i class="bi bi-map-fill me-2"></i>Legend</h6>
                        </div>
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2">🚛</span>
                                <small>Active Trucks</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning me-2">🟡</span>
                                <small>Idle Trucks</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-danger me-2">🔧</span>
                                <small>Maintenance</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2">🏢</span>
                                <small>Depot/Base</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">📍</span>
                                <small>Pickup Points</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fleet Overview Panel -->
                <div class="position-absolute top-0 start-0 m-3" style="z-index: 1000;">
                    <div class="card shadow-sm border-0" style="width: 250px;">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Fleet Overview</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Active:</span>
                                <span class="badge bg-success" id="active-count">0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Idle:</span>
                                <span class="badge bg-warning" id="idle-count">0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Maintenance:</span>
                                <span class="badge bg-info" id="maintenance-count"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(session('user_role')== 'municipality_administrator')
        <!-- Active Fleet Status Table -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Active Fleet Status
                    </h5>
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <input type="text" id="truck-search" class="form-control form-control-sm" placeholder="🔍 Search trucks or drivers..." style="min-width: 250px;">
                        <button class="btn btn-sm btn-info" onclick="exportFleetData()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold"><i class="bi bi-hash me-1"></i>Truck ID</th>
                                <th class="fw-semibold"><i class="bi bi-person-fill me-1"></i>Driver</th>
                                <th class="fw-semibold"><i class="bi bi-geo-alt-fill me-1"></i>Current Location</th>
                                <th class="fw-semibold"><i class="bi bi-map me-1"></i>Route</th>
                                <th class="fw-semibold"><i class="bi bi-graph-up me-1"></i>Progress</th>
                                <th class="fw-semibold"><i class="bi bi-clock me-1"></i>ETA</th>
                                <th class="fw-semibold"><i class="bi bi-fuel-pump-fill me-1"></i>Fuel</th>
                                <th class="fw-semibold"><i class="bi bi-circle-fill me-1"></i>Status</th>
                                <th class="fw-semibold text-center"><i class="bi bi-gear-fill me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="truck-status-table">
                            @foreach ($trucks as $truck)
                                <tr>
                                    <td class="fw-medium">{{ $truck->truck_id }}</td>
                                    <td>{{ $truck->driver_name ?? 'N/A' }}</td>
                                    <td class="current-location text-muted" data-lat="{{ $truck->current_latitude }}" data-lng="{{ $truck->current_longitude }}">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span>Loading location...</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($truck->pickups)
                                            @php
                                                $pickups = is_array($truck->pickups) ? $truck->pickups : json_decode($truck->pickups, true);
                                            @endphp
                                            <button class="btn btn-sm btn-outline-info show-address-btn" 
                                                    data-pickups='@json($pickups)'>
                                                <i class="bi bi-eye-fill me-1"></i>Show Addresses
                                            </button>
                                        @else
                                            <span class="text-muted">No route</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($truck->progress ?? false)
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px; min-width: 80px;">
                                                    @php
                                                        $progress = explode('/', $truck->progress);
                                                        $percentage = count($progress) == 2 && $progress[1] > 0 
                                                            ? ($progress[0] / $progress[1]) * 100 
                                                            : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $truck->progress }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">0/0</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">—</td>
                                    <td>
                                        @if($truck->initial_fuel)
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px; min-width: 60px;">
                                                    <div class="progress-bar bg-{{ $truck->initial_fuel > 50 ? 'success' : ($truck->initial_fuel > 25 ? 'warning' : 'danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $truck->initial_fuel }}%"></div>
                                                </div>
                                                <small>{{ $truck->initial_fuel }}%</small>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($truck->status)
                                            @if(strtolower($truck->status) === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @elseif(strtolower($truck->status) === 'idle')
                                                <span class="badge bg-warning">Idle</span>
                                            @elseif(strtolower($truck->status) === 'maintenance')
                                                <span class="badge bg-danger">Maintenance</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $truck->status }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Edit / Assign Route Button -->
                                             <button class="btn btn-outline-primary assign-route-btn"
                                                    data-truck-id="{{ $truck->id }}"
                                                    title="Assign Route"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#assignRouteModal">
                                                <i class="bi bi-map-fill"></i>
                                            </button>


                                            <form action="{{ route('truck.setIdle', $truck->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')

                                                @if($truck->status === 'active')
                                                    <button class="btn btn-outline-danger" title="Set to Idle">
                                                        <i class="bi bi-pause-circle"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-success" title="Set to Active">
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                @endif
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>

        @endif
    </div>
</div>

<!-- Pickup Address Modal -->
<div class="modal fade" id="pickupAddressModal" tabindex="-1" aria-labelledby="pickupAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="pickupAddressModalLabel">
                    <i class="bi bi-geo-alt-fill me-2"></i>Pickup Addresses
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="pickup-address-modal-body">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading addresses...</p>
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

<!-- Assign New Route Modal -->
<div class="modal fade" id="assignRouteModal" tabindex="-1" aria-labelledby="assignRouteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="assignRouteLabel">
                    <i class="bi bi-geo-alt-fill me-2"></i>Assign New Route to Truck
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Instructions:</strong> Click on the map to add pickup locations. You can drag markers to adjust positions. To add timestamps click the pin icon 
                </div>
                <!-- Map container -->
                <div id="pickupMap" style="height: 500px; border-radius: 8px; border: 2px solid #dee2e6;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="savePickupPoints()">
                    <i class="bi bi-save-fill me-1"></i>Save Pickups
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        async function updateTruckCounts() {
    try {
        const response = await fetch('/truck-pickups'); // your endpoint
        const trucks = await response.json();

        let active = 0, idle = 0, maintenance = 0;

        trucks.forEach(truck => {
            switch(truck.status) {
                case 'active': active++; break;
                case 'idle': idle++; break;
                case 'maintenance': maintenance++; break;
            }
        });

        document.getElementById('active-count').innerText = active;
        document.getElementById('idle-count').innerText = idle;
        document.getElementById('maintenance-count').innerText = maintenance;

    } catch (error) {
        console.error('Error fetching truck counts:', error);
    }
}

// Call the function on page load
updateTruckCounts();
        // Pickup Address Modal Handler
        const buttons = document.querySelectorAll('.show-address-btn');
        const modalBody = document.getElementById('pickup-address-modal-body');
        const pickupModalEl = document.getElementById('pickupAddressModal');
        const pickupModal = new bootstrap.Modal(pickupModalEl);

        buttons.forEach(btn => {
            btn.addEventListener('click', async () => {
                const pickups = JSON.parse(btn.dataset.pickups);

                // Show modal with loading state
                modalBody.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Fetching addresses from OpenStreetMap...</p>
                    </div>
                `;
                pickupModal.show();

                let addressesHtml = '<div class="list-group">';
                let count = 1;

                for (const pickup of pickups) {
                    const lat = pickup.lat;
                    const lng = pickup.lng;

                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
                        const data = await res.json();
                        const address = data.display_name || `${lat}, ${lng}`;
                        addressesHtml += `
                            <div class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-primary me-2 mt-1">${count}</span>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Pickup Location ${count}</small>
                                        <p class="mb-0">${address}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    } catch (err) {
                        addressesHtml += `
                            <div class="list-group-item">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-secondary me-2 mt-1">${count}</span>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Pickup Location ${count}</small>
                                        <p class="mb-0">${lat}, ${lng} <span class="text-warning">(Location unavailable)</span></p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    count++;
                }

                addressesHtml += '</div>';
                modalBody.innerHTML = addressesHtml;
            });
        });

        // Current Location Reverse Geocoding
        const locationCells = document.querySelectorAll('.current-location');

        locationCells.forEach(async cell => {
            const lat = cell.dataset.lat;
            const lng = cell.dataset.lng;

            if (lat && lng) {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18`);
                    const data = await response.json();
                    const address = data.display_name || `${lat}, ${lng}`;
                    cell.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                            <span class="small">${address}</span>
                        </div>
                    `;
                } catch (err) {
                    cell.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <span class="small">${lat}, ${lng} (Location unavailable)</span>
                        </div>
                    `;
                }
            } else {
                cell.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-question-circle-fill text-muted me-2"></i>
                        <span class="small text-muted">No location data</span>
                    </div>
                `;
            }
        });

        // Truck Search Functionality
        const searchInput = document.getElementById('truck-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#truck-status-table tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });

    // Placeholder functions for button handlers
    function toggleAutoRefresh() {
        const statusSpan = document.getElementById('refresh-status');
        const currentStatus = statusSpan.textContent.includes('ON');
        statusSpan.textContent = currentStatus ? 'Auto Refresh: OFF' : 'Auto Refresh: ON';
    }

    function centerMapOnFleet() {
        console.log('Centering map on fleet...');
    }

    function toggleTrafficLayer() {
        console.log('Toggling traffic layer...');
    }

    function optimizeRoute() {
        console.log('Optimizing route...');
    }

    function showRouteDetails() {
        console.log('Showing route details...');
    }

    function exportFleetData() {
        console.log('Exporting fleet data...');
    }

    function savePickupPoints() {
        console.log('Saving pickup points...');
    }
</script>

@endsection