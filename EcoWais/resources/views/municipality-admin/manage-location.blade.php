@extends('layouts.app')

@section('content')

<div class="container">

    <button 
        class="btn btn-primary mb-3"
        data-bs-toggle="modal" 
        data-bs-target="#manageLocationsModal"
    >
        <i class="bi bi-geo-alt-fill me-1"></i> Add Barangay
    </button>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Locations Table -->
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-light border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-map-fill me-2 text-primary"></i>Barangay Locations
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-start">
                    <thead class="table-primary text-dark">
                        <tr>
                            <th>#</th>
                            <th>Barangay</th>
                            <th>Admin</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($locations as $loc)
                        <tr>
                            <td class="fw-semibold">{{ $loc->id }}</td>
                            <td class="fw-semibold">{{ $loc->location }}</td>

                            <td>
                                @php
                                $admin = $users->where('id', $loc->adminId)->first();
                                @endphp

                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill">
                                    {{ $admin ? $admin->name : 'N/A' }}
                                </span>
                            </td>

                            <td>{{ $loc->latitude }}</td>
                            <td>{{ $loc->longitude }}</td>
                            <td>{{ $loc->created_at->format('M d, Y') }}</td>

                            <td>
                                <button class="btn btn-sm btn-warning shadow-sm"
                                    onclick="openAssignModal({{ $loc->id }}, '{{ $loc->location }}')">
                                    <i class="bi bi-person-lines-fill me-1"></i>Assign Admin
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


<!-- Assign Admin Modal -->
<div class="modal fade" id="assignAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow">

            <form method="POST" action="{{ route('locations.assignAdmin') }}">
                @csrf

                <input type="hidden" id="assign_location_id" name="location_id">

                <div class="modal-header bg-warning text-dark rounded-top-4">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-check-fill me-2"></i>Assign Admin to Barangay
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Selected Barangay:</label>
                        <p id="assign_location_name" class="fs-5 text-primary fw-semibold mb-0"></p>
                    </div>

                    <label class="form-label fw-semibold">Select Admin</label>
                    <select class="form-select rounded-3 shadow-sm" name="adminId" required>
                        <option value="">— Select Admin —</option>

                        @php
                        $unassignedAdmins = $users->filter(function($user) {
                            return $user->role === 'barangay_admin' && !\App\Models\Location::where('adminId', $user->id)->exists();
                        });
                        @endphp

                        @if($unassignedAdmins->isEmpty())
                            <option value="" disabled>No Admin Available</option>
                        @else
                            @foreach($unassignedAdmins as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning fw-semibold px-4">
                        <i class="bi bi-check2-circle me-1"></i>Assign
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- Add Barangay Modal -->
<div class="modal fade" id="manageLocationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow">

            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-geo-alt-fill me-2"></i>Add New Barangay
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('locations.store') }}">
                @csrf

                <div class="modal-body">

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-circle-fill me-2"></i>Please correct the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info border-0 shadow-sm rounded-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Click the map to select coordinates. Barangay name will auto-detect if possible.
                    </div>

                    <label class="form-label fw-semibold">Pin Location</label>
                    <div id="map"
                        style="height: 350px; width: 100%; border-radius: 12px; border: 2px solid #dce3ea;">
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Barangay Name</label>
                        <input type="text" 
                               class="form-control rounded-3 shadow-sm @error('location') is-invalid @enderror" 
                               id="location" 
                               name="location" 
                               value="{{ old('location') }}"
                               required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Latitude</label>
                            <input type="text" 
                                   class="form-control rounded-3 shadow-sm @error('latitude') is-invalid @enderror" 
                                   id="latitude" 
                                   name="latitude" 
                                   value="{{ old('latitude') }}"
                                   readonly 
                                   required>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Longitude</label>
                            <input type="text" 
                                   class="form-control rounded-3 shadow-sm @error('longitude') is-invalid @enderror" 
                                   id="longitude" 
                                   name="longitude" 
                                   value="{{ old('longitude') }}"
                                   readonly 
                                   required>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save-fill me-1"></i>Save Barangay
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function openAssignModal(id, name) {
    document.getElementById('assign_location_id').value = id;
    document.getElementById('assign_location_name').innerText = name;

    new bootstrap.Modal(document.getElementById('assignAdminModal')).show();
}

// MAP SCRIPT
document.addEventListener('DOMContentLoaded', function () {
    let map;
    let marker;

    const modalEl = document.getElementById('manageLocationsModal');

    // Reopen modal if there are validation errors
    @if ($errors->any())
        new bootstrap.Modal(modalEl).show();
    @endif

    modalEl.addEventListener('shown.bs.modal', function () {

        if (!map) {
            map = L.map('map').setView([13.411, 121.180], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            map.on('click', function(e) {
                if (marker) map.removeLayer(marker);
                marker = L.marker(e.latlng, { draggable: true }).addTo(map);

                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);

                // Reverse geocode to get barangay name
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.address) {
                            const barangay = data.address.suburb || data.address.village || data.address.hamlet || '';
                            if (barangay) {
                                document.getElementById('location').value = barangay;
                            }
                        }
                    })
                    .catch(err => console.warn('Reverse geocoding failed', err));

                marker.on('dragend', function(ev) {
                    const p = ev.target.getLatLng();
                    document.getElementById('latitude').value = p.lat.toFixed(6);
                    document.getElementById('longitude').value = p.lng.toFixed(6);

                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${p.lat}&lon=${p.lng}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.address) {
                                const barangay = data.address.suburb || data.address.village || data.address.hamlet || '';
                                if (barangay) {
                                    document.getElementById('location').value = barangay;
                                }
                            }
                        })
                        .catch(err => console.warn('Reverse geocoding failed', err));
                });
            });
        }

        setTimeout(() => map.invalidateSize(), 200);
    });
});
</script>

@endsection