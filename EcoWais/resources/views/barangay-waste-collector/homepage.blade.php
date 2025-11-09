@extends('layouts.app')

@section('content')
    <div id="driver-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Driver Interface</h2>

            <div class="card">
                <h3>Today's Routes - <span id="driver-name">Driver #001</span></h3>
                <div id="driver-status" class="alert alert-success">
                    Status: On Route | Truck #5 | Next Stop: Barangay San Antonio
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Address</th>
                            <th>Waste Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="driver-routes">
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="form-group">
                    <label>Status Update</label>
                    <select id="driver-status-select">
                        <option value="on-route">On Route</option>
                        <option value="at-pickup">At Pickup Location</option>
                        <option value="break">On Break</option>
                        <option value="returning">Returning to Base</option>
                    </select>
                    <button class="btn" onclick="updateDriverStatus()">Update Status</button>
                </div>
            </div>

            <div class="card">
                <h3>Report Issue</h3>
                <form id="driver-report-form">
                    <div class="form-group">
                        <label>Issue Type</label>
                        <select id="driver-issue-type" required>
                            <option value="">Select issue</option>
                            <option value="vehicle">Vehicle Problem</option>
                            <option value="access">Access Problem</option>
                            <option value="safety">Safety Concern</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="driver-issue-desc" required placeholder="Describe the issue"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-full">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
@endsection