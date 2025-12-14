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
            <option value="{{ $location->id }}" data-location-name="{{ $location->location }}">
                {{ $location->location }}
            </option>
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
        <option value="">— Select a barangay first —</option>
        @foreach($trucks as $truck)
            @if($truck->status == 'idle')
                <option value="{{ $truck->id }}" 
                        data-initial-location="{{ $truck->initial_location }}"
                        style="display: none;">
                    {{ $truck->truck_id }}
                    @if($truck->driver && $truck->driver->user)
                        - {{ $truck->driver->user->name }}
                    @endif
                </option>
            @endif
        @endforeach
    </select>
    <small class="text-muted mt-1 d-block">
        <i class="bi bi-info-circle me-1"></i>Only trucks assigned to the selected barangay will appear
    </small>
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
                            <div class="card-body">
                                    <div class="row mb-3 g-2">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-muted">
                                                <i class="bi bi-calendar-event me-1"></i>FILTER BY DATE
                                            </label>
                                            <input type="date" id="schedule-date-filter" class="form-control" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-muted">
                                                <i class="bi bi-truck-front-fill me-1"></i>FILTER BY TRUCK
                                            </label>
                                            <select id="schedule-truck-filter" class="form-select">
                                                <option value="">All Trucks</option>
                                                @foreach($pickups->unique('truck_id')->pluck('truck.truck_id')->filter() as $truckId)
                                                    <option value="{{ $truckId }}">Truck {{ $truckId }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-muted">
                                                <i class="bi bi-geo-alt-fill me-1"></i>FILTER BY BARANGAY
                                            </label>
                                            <select id="schedule-barangay-filter" class="form-select">
                                                <option value="">All Barangays</option>
                                                @foreach($pickups->unique('location.location')->pluck('location.location')->filter() as $barangay)
                                                    <option value="{{ $barangay }}">{{ $barangay }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <button id="clear-all-filters" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Clear all filters
                                        </button>
                                    </div>
                                </div>
                            <div>
                                <table class="table table-hover align-middle mb-0">
                                    <!-- Your existing table code -->
                                </table>
                            </div>
                            
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

    document.addEventListener('DOMContentLoaded', () => {

        const barangaySelect = document.getElementById('initial_location');
    const truckSelect = document.getElementById('truck');
    
    if (barangaySelect && truckSelect) {
        barangaySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const selectedLocationName = selectedOption.getAttribute('data-location-name');
            
            // Reset truck dropdown
            truckSelect.value = '';
            
            // Get all truck options
            const truckOptions = truckSelect.querySelectorAll('option');
            
            let availableTrucks = 0;
            
            // Show/hide trucks based on selected barangay
            truckOptions.forEach((option, index) => {
                if (index === 0) return; // Skip the first "Select a barangay first" option
                
                const truckLocation = option.getAttribute('data-initial-location');
                
                if (selectedLocationName && truckLocation === selectedLocationName) {
                    option.style.display = '';
                    availableTrucks++;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Update first option text based on availability
            if (selectedLocationName) {
                if (availableTrucks > 0) {
                    truckOptions[0].textContent = `— Select a truck (${availableTrucks} available) —`;
                } else {
                    truckOptions[0].textContent = '— No trucks available for this barangay —';
                }
            } else {
                truckOptions[0].textContent = '— Select a barangay first —';
            }
        });
    }
    const dateFilter = document.getElementById('schedule-date-filter');
    const truckFilter = document.getElementById('schedule-truck-filter');
    const barangayFilter = document.getElementById('schedule-barangay-filter');
    const clearAllBtn = document.getElementById('clear-all-filters');
    const tableBody = document.getElementById('admin-barangay-schedule-table');
    
    if (!tableBody) return;
    
    // Filter function
    const filterTable = () => {
        const selectedDate = dateFilter ? dateFilter.value : '';
        const selectedTruck = truckFilter ? truckFilter.value : '';
        const selectedBarangay = barangayFilter ? barangayFilter.value : '';
        
        const rows = tableBody.getElementsByTagName('tr');
        let visibleCount = 0;
        
        // Remove any existing "no results" row
        const existingNoResults = document.getElementById('no-results-row');
        if (existingNoResults) existingNoResults.remove();
        
        Array.from(rows).forEach(row => {
            // Skip empty state row
            if (row.querySelector('td[colspan]')) {
                row.style.display = 'none';
                return;
            }
            
            // Skip if not enough cells
            if (row.cells.length < 5) return;
            
            let showRow = true;
            
            // Filter by Date
            if (selectedDate) {
                const dateCell = row.cells[1];
                const dateBadge = dateCell.querySelector('.badge');
                
                if (dateBadge) {
                    const rowDateText = dateBadge.textContent.trim();
                    const rowDate = new Date(rowDateText);
                    const filterDate = new Date(selectedDate);
                    
                    const rowDateOnly = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
                    const filterDateOnly = new Date(filterDate.getFullYear(), filterDate.getMonth(), filterDate.getDate());
                    
                    if (rowDateOnly.getTime() !== filterDateOnly.getTime()) {
                        showRow = false;
                    }
                }
            }
            
            // Filter by Truck
            // Filter by Truck
if (selectedTruck && showRow) {
    const truckCell = row.cells[3];
    
    // Try to find the truck ID in the cell
    const truckIdElement = truckCell.querySelector('span');
    let truckId = '';
    
    if (truckIdElement) {
        truckId = truckIdElement.textContent.trim();
    } else {
        // Fallback: check if it says "Unassigned"
        truckId = truckCell.textContent.trim();
    }
    
    // Exact match only
    if (truckId !== selectedTruck && !truckId.includes('Unassigned')) {
        showRow = false;
    } else if (truckId.includes('Unassigned') && selectedTruck !== '') {
        showRow = false;
    }
}
            
            // Filter by Barangay
            if (selectedBarangay && showRow) {
                const barangayCell = row.cells[0];
                const barangayText = barangayCell.textContent.trim();
                
                if (!barangayText.includes(selectedBarangay)) {
                    showRow = false;
                }
            }
            
            // Show or hide row
            if (showRow) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "no results" message if no rows visible
        if (visibleCount === 0) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.id = 'no-results-row';
            
            let filterMessage = 'No pickups found';
            const activeFilters = [];
            
            if (selectedDate) activeFilters.push(`date: ${selectedDate}`);
            if (selectedTruck) activeFilters.push(`truck: ${selectedTruck}`);
            if (selectedBarangay) activeFilters.push(`barangay: ${selectedBarangay}`);
            
            if (activeFilters.length > 0) {
                filterMessage += ' for ' + activeFilters.join(', ');
            }
            
            noResultsRow.innerHTML = `
                <td colspan="5" class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p class="mb-0">${filterMessage}</p>
                        <small>Try adjusting your filters</small>
                    </div>
                </td>
            `;
            tableBody.appendChild(noResultsRow);
        }
        
        console.log(`✅ Filters applied - Showing ${visibleCount} rows`);
    };
    
    // Add event listeners
    if (dateFilter) {
        dateFilter.addEventListener('change', filterTable);
    }
    
    if (truckFilter) {
        truckFilter.addEventListener('change', filterTable);
    }
    
    if (barangayFilter) {
        barangayFilter.addEventListener('change', filterTable);
    }
    
    // Clear all filters
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', () => {
            if (dateFilter) dateFilter.value = '';
            if (truckFilter) truckFilter.value = '';
            if (barangayFilter) barangayFilter.value = '';
            filterTable();
        });
    }
});
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