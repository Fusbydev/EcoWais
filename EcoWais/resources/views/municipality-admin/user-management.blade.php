@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="ext-center fw-bold text-white">User Management</h2>

    <!-- Actions -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus-circle me-1"></i> Add New User
        </button>
    </div>

    <!-- User Table Card -->
    <div class="card shadow-sm border-0">
        
        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Error Message --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger m-3">
                <strong>There were some problems:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">All Users</h5>
        </div>

        <div class="card-body p-0">
            <div class="row mb-3">
    
            <div class="table-responsive">
    <div class="row mb-3 mt-3">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    type="text" 
                    id="userSearch" 
                    class="form-control border-start-0" 
                    placeholder="Search by name..."
                >
            </div>
        </div>
    </div>
</div>



                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th class="text-dark">#</th>
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Role</th>
                            <th class="text-dark">Email</th>
                            <th class="text-dark">Status</th>
                            <th class="text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start">{{ $user->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->status === 'activated' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $user->id }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>

                                    @if ($user->status === 'activated')
                                        <form action="{{ route('users.deactivate', $user->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-warning"><i class="bi bi-slash-circle"></i> Deactivate</button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', $user->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i> Activate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div> <!-- End Card -->

    <!-- ADD USER MODAL -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addUserLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">

                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input 
                                    type="text"
                                    name="phone"
                                    class="form-control phone-input"
                                    id="create-phone"
                                    placeholder="Enter phone number"
                                    minlength="11"
                                    maxlength="11"
                                    required
                                >
                                <div class="text-danger small mt-1 d-none" id="create-phone-error">
                                    Phone number must be exactly 11 digits.
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="barangay_waste_collector">Driver</option>
                                    <option value="barangay_admin">Barangay Admin</option>
                                </select>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="activated">Activated</option>
                                    <option value="deactivated">Deactivated</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save User</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- EDIT USER MODALS -->
    @foreach($users as $user)
    <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-labelledby="editUserLabel-{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editUserLabel-{{ $user->id }}">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input 
                                type="text"
                                name="phone"
                                id="phone-{{ $user->id }}"
                                class="form-control phone-input"
                                value="{{ $user->phone_number }}"
                                required
                                maxlength="11"
                                minlength="11"
                            >
                            <div class="text-danger small mt-1 d-none" id="phone-error-{{ $user->id }}">
                                Phone number must be exactly 11 digits.
                            </div>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" disabled>
                                <option value="resident" {{ $user->role === 'resident' ? 'selected' : '' }}>Resident</option>
                                <option value="barangay_waste_collector" {{ $user->role === 'barangay_waste_collector' ? 'selected' : '' }}>Barangay Waste Collector</option>
                                <option value="barangay_admin" {{ $user->role === 'barangay_admin' ? 'selected' : '' }}>Barangay Admin</option>
                                <option value="municipality_administrator" {{ $user->role === 'municipality_administrator' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="activated" {{ $user->status === 'activated' ? 'selected' : '' }}>Activated</option>
                                <option value="deactivated" {{ $user->status === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-info text-white"><i class="bi bi-pencil-square"></i> Update User</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endforeach

</div>

<!-- Global Phone Validator -->
<script>

    document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("userSearch");
    const tableRows = document.querySelectorAll("table tbody tr");

    searchInput.addEventListener("input", function() {
        const query = this.value.toLowerCase().trim();

        tableRows.forEach(row => {
            const nameCell = row.querySelector("td:nth-child(2)").textContent.toLowerCase();
            if(nameCell.includes(query)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});


function validatePhoneField(input, errorDiv) {
    input.value = input.value.replace(/[^0-9]/g, '').slice(0, 11);
    if (input.value.length !== 11) {
        errorDiv.classList.remove("d-none");
    } else {
        errorDiv.classList.add("d-none");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".phone-input").forEach(function(input) {
        let errorDiv = document.getElementById(input.id + "-error");

        input.addEventListener("input", function() {
            validatePhoneField(input, errorDiv);
        });

        input.closest("form")?.addEventListener("submit", function(e) {
            if(input.value.length !== 11) {
                e.preventDefault();
                errorDiv.classList.remove("d-none");
            }
        });
    });
});
</script>

@endsection
