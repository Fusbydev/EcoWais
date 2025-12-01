<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Consolidated Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }

        h1, h2 {
            text-align: center;
            color: #0b3d91;
        }

        h2 {
            margin-top: 40px;
            margin-bottom: 10px;
            font-size: 16px;
            text-align: left;
            color: #0b3d91;
            border-bottom: 2px solid #0b3d91;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
        }

        ul {
            margin: 5px 0 15px 20px;
            padding: 0;
        }

        img {
            display: block;
            max-width: 60px;
            max-height: 60px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #999;
            padding-top: 5px;
        }

        .section-title {
            background-color: #f0f4f8;
            padding: 5px;
        }
    </style>
</head>
<body>

    <h1>Municipal Waste Management</h1>
    <p style="text-align:center;">Consolidated Report</p>
    <hr>
<h2>Summary Report</h2>
<p>
    As of {{ now()->format('F j, Y') }}, the municipal waste management operations have recorded 
    <strong>{{ $reports['collection']->total_pickups }}</strong> total pickups. Of these, 
    <strong>{{ $reports['collection']->completed_pickups }}</strong> pickups were successfully completed, 
    while <strong>{{ $reports['collection']->missed_pickups }}</strong> pickups were missed. 
    A total of <strong>{{ $reports['fleet']->count() }}</strong> trucks were deployed, with 
    <strong>{{ $reports['environment']->trucks_used }}</strong> trucks actively used in operations. 
    Driver performance reports indicate <strong>{{ $reports['driverIssues']->count() }}</strong> issues reported by drivers, 
    and residents have submitted <strong>{{ $reports['residentIssues']->count() }}</strong> issue reports, 
    categorized by type and priority. Overall, the operational and environmental performance metrics 
    provide a comprehensive view of the waste collection efficiency and service reliability for the municipality.
</p>

    <!-- Fleet Performance -->
    <h2>Fleet Performance Report</h2>
    <table>
        <tr>
            <th>Truck ID</th>
            <th>Total Pickups</th>
            <th>Drivers Present</th>
            <th>Issues Reported</th>
        </tr>
        @foreach($reports['fleet'] as $f)
        <tr>
            <td>{{ $f->truck_id }}</td>
            <td>{{ $f->total_pickups }}</td>
            <td>{{ $f->drivers_present }}</td>
            <td>{{ $f->issues_reported }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Dashboard Section -->
<h2>Waste Dashboard</h2>
<table style="width:100%; margin-bottom:20px;">
    <tr>
        <td><strong>Waste Today:</strong> {{ $reports['todayTotal'] ?? 0 }} kg</td>
        <td><strong>Waste This Month:</strong> {{ $reports['monthTotal'] ?? 0 }} kg</td>
        <td><strong>Total Collections:</strong> {{ $reports['totalCollections'] ?? 0 }}</td>
    </tr>
</table>

    <!-- Collection Efficiency -->
    <h2>Collection Efficiency Report</h2>
    <ul>
        <li><strong>Total Pickups:</strong> {{ $reports['collection']->total_pickups }}</li>
        <li><strong>Completed Pickups:</strong> {{ $reports['collection']->completed_pickups }}</li>
        <li><strong>Missed Pickups:</strong> {{ $reports['collection']->missed_pickups }}</li>
    </ul>

    <!-- Resident Issues -->
    <h2>Resident Issue Report</h2>
    <table>
        <tr>
            <th>Issue Type</th>
            <th>Other Issue</th>
            <th>Location</th>
            <th>Incident Date</th>
            <th>Priority</th>
            <th>Description</th>
            <th>Photo</th>
        </tr>
        @foreach($reports['residentIssues'] as $r)
        <tr>
            <td>{{ $r->issue_type }}</td>
            <td>{{ $r->other_issue }}</td>
            <td>{{ $r->location }}</td>
            <td>{{ $r->incident_datetime }}</td>
            <td>{{ ucfirst($r->priority) }}</td>
            <td>{{ $r->description }}</td>
            <td>
                @if($r->photo_path)
                    <img src="{{ public_path($r->photo_path) }}">
                @else
                    N/A
                @endif
            </td>
        </tr>
        @endforeach
    </table>

    <!-- Driver Issues -->
    <h2>Driver Issue Report</h2>
    <table>
        <tr>
            <th>Driver ID</th>
            <th>Issue Type</th>
            <th>Description</th>
            <th>Reported At</th>
        </tr>
        @foreach($reports['driverIssues'] as $d)
        <tr>
            <td>{{ $d->driver_id }}</td>
            <td>{{ $d->issue_type }}</td>
            <td>{{ $d->description }}</td>
            <td>{{ $d->created_at }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Environmental Impact -->
    <h2>Environmental Impact Report</h2>
    <ul>
        <li><strong>Trucks Used:</strong> {{ $reports['environment']->trucks_used }}</li>
        <li><strong>Total Pickups:</strong> {{ $reports['environment']->total_pickups }}</li>
    </ul>

    <div class="footer">
        Generated on {{ now()->format('F j, Y, g:i A') }}
    </div>

</body>
</html>
