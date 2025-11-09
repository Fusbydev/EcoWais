@extends ('layouts.app')

@section ('content')
    <div id="dashboard-page" class="page">
        <div class="dashboard-header">
            <h1>EcoWais Dashboard</h1>
           
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="active-trucks">12</div>
                <div>Active Trucks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="scheduled-pickups">248</div>
                <div>Scheduled Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="completion-rate">94%</div>
                <div>Completion Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="reports-count">7</div>
                <div>Pending Reports</div>
            </div>
        </div>

        <div class="card">
            <h3>Recent Activity</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Activity</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="activity-log">
                </tbody>
            </table>
        </div>
    </div>
@endsection