@extends ('layouts.app')

@section ('content')
   <div id="admin-barangay-page" class="page">
    <div class="container">
        <h2 style="color: white; text-align: center; margin-bottom: 2rem;">
            🗓️ Barangay Waste Pickup & Disposal Scheduling (Admin)
        </h2>

        <div class="card">

        @if (session('pickupSuccess'))
            <div class="alert alert-success">
                {{ session('pickupSuccess') }}
            </div>
        @endif

            <h3>📋 Schedule Pickup per Barangay</h3>
            <form action="{{ route('pickup.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Select Barangay</label>
                    <select class="form-control" id="initial_location" name="initial_location" required>
                        <option value="">-- Select Location --</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}">{{ $location->location }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" id="admin-pickup-date" name="admin-pickup-date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Pickup Time</label>
                    <input type="time" id="admin-pickup-time" name="admin-pickup-time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Assigned Truck</label>
                    <select class="form-control" id="truck" name="truck" required>
                        <option value="">Select Truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}">{{ $truck->truck_id }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-success btn-full">✅ Schedule Pickup</button>
            </form>
        </div>

        <div class="card">
            <h3>🧾 Scheduled Barangay Pickups</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Truck</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="admin-barangay-schedule-table">
                    @forelse ($pickups as $pickup)
                        <tr>
                            <td>{{ $pickup->location->location ?? 'N/A' }}</td>
                            <td>{{ $pickup->pickup_date }}</td>
                            <td>{{ \Carbon\Carbon::parse($pickup->pickup_time)->format('h:i A') }}</td>
                            <td>{{ $pickup->truck->truck_id ?? 'Unassigned' }}</td>
                            <td class="text-center">
                                <form action="{{ route('pickup.destroy', $pickup->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No scheduled pickups yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection