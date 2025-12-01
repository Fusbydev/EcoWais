@extends ('layouts.app')

@section ('content')

<div class="container mt-4">
    <h2 class="mb-4 text-center">👥 User Management</h2>

    <!-- Actions -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            Add New User
        </button>
    </div>

    <!-- User Table -->
    <div class="card shadow-sm">
        {{-- Success Message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Error Message --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some problems:</strong>
        <ul class="mt-2 mb-0">
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
            <table class="table table-striped mb-0">
                <thead class="table-dark">
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
                    @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->status === 'activated' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editUserModal-{{ $user->id }}">
                                    Edit
                                </button>

                                @if ($user->status === 'activated')
                                    <form action="{{ route('users.deactivate', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-warning">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('users.activate', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Larger modal -->
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="container-fluid">
                        <div class="row g-3"> <!-- Grid gap for spacing -->

                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="number" name="phone" class="form-control" placeholder="Enter phone number" required>
                            </div>

                            <!-- Role -->
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="barangay_waste_collector">Driver</option>
                                    <option value="barangay_admin">Barangay Admin</option>
                                    <option value="municipality_administrator">Municipality Admin</option>
                                </select>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="activated">Activated</option>
                                    <option value="deactivated">Deactivated</option>
                                </select>
                            </div>

                        </div> <!-- End Row -->
                    </div> <!-- End Container -->

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div> <!-- End Modal Body -->

        </div>
    </div>
</div>


    <!-- EDIT USER MODALS (Placed AFTER the table to prevent flicker) -->
    @foreach ($users as $user)
    <div class="modal fade" id="editUserModal-{{ $user->id }}" tabindex="-1" aria-labelledby="editUserLabel-{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editUserLabel-{{ $user->id }}">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="resident" {{ $user->role === 'resident' ? 'selected' : '' }}>Resident</option>
                                <option value="barangay_waste_collector" {{ $user->role === 'barangay_waste_collector' ? 'selected' : '' }}>barangay_waste_collector</option>
                                <option value="barangay_admin" {{ $user->role === 'barangay_admin' ? 'selected' : '' }}>Barangay Admin</option>
                                <option value="municipality_administrator" {{ $user->role === 'municipality_administrator' ? 'selected' : '' }}>Municipality Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="activated" {{ $user->status === 'activated' ? 'selected' : '' }}>Activated</option>
                                <option value="deactivated" {{ $user->status === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-info text-white">Update User</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    @endforeach

</div>

@endsection
