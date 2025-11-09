@extends('layouts.app')
    @section ('content')
    <div id="resident-page" class="page">
        <div class="container">
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Barangay Admin Portal</h2>

            <!-- Barangay Selection -->
            <div class="card">
                <h3>📍 Barangay Information</h3>
                <div class="form-group">
                    <label>Select Your Barangay</label>
                    <select id="barangay-select" onchange="loadBarangayData()">
                        <option value="suqui">Barangay Suqui</option>
                        <option value="san-vicente">Barangay San Vicente</option>
                        <option value="lalud">Barangay Lalud</option>
                        <option value="guinobatan">Barangay Guinobatan</option>
                        <option value="bayanan">Barangay Bayanan I</option>
                        <option value="lumangbayan">Barangay Lumangbayan</option>
                    </select>
                </div>
                <div id="barangay-info" style="margin-top: 1rem;">
                    <p><strong>Barangay:</strong> <span id="current-barangay">Barangay Suqui</span></p>
                    <p><strong>Assigned Collectors:</strong> <span id="assigned-collectors">3</span></p>
                    <p><strong>Collection Days:</strong> Monday, Wednesday, Friday</p>
                </div>
            </div>

            <!-- Driver/Collector Attendance Tracking -->
            <div class="card">
                <h3>👥 Driver/Collector Attendance</h3>
                <div class="alert alert-info" style="margin-bottom: 1rem;">
                    <strong>ℹ️ How to Mark Attendance:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        <li>Click "✓ Mark Attendance" to record when a driver/collector arrives</li>
                        <li>Click "⏰ Time Out" button to record when they finish their shift</li>
                        <li>Mark as "Present", "Late", or "Absent" based on their arrival time</li>
                        <li>Standard shift: 1:00 AM - 5:00 PM (Late if after 7:00 AM)</li>
                    </ul>
                </div>
                <div class="search-filter">
                    <input type="date" id="attendance-date" value="">
                    <button class="btn btn-info" onclick="loadAttendanceData()">📅 Load Attendance</button>
                    <button class="btn btn-success" onclick="showMarkAttendance()">✓ Mark Attendance</button>
                    <button class="btn btn-warning" onclick="showQuickAttendance()">⚡ Quick Mark All</button>
                    <button class="btn btn-secondary" onclick="exportAttendance()">📊 Export</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Driver/Collector Name</th>
                                <th>Role</th>
                                <th>Truck ID</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours Worked</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-table">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card" style="background: rgba(40, 167, 69, 0.2); border: 2px solid #28a745;">
                    <div class="stat-number" id="present-count" style="color: #28a745;">0</div>
                    <div style="color: #28a745;">Present Today</div>
                </div>
                <div class="stat-card" style="background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545;">
                    <div class="stat-number" id="absent-count" style="color: #dc3545;">0</div>
                    <div style="color: #dc3545;">Absent Today</div>
                </div>
                <div class="stat-card" style="background: rgba(255, 193, 7, 0.2); border: 2px solid #ffc107;">
                    <div class="stat-number" id="late-count" style="color: #856404;">0</div>
                    <div style="color: #856404;">Late Arrivals</div>
                </div>
            </div>

            <!-- Report an Issue -->
            <div class="card">
                <h3>📢 Report an Issue</h3>
                <form id="report-form">
                    <div class="form-group">
                        <label>Issue Type</label>
                        <select id="issue-type" required>
                            <option value="">Select issue type</option>
                            <option value="missed">Missed Collection</option>
                            <option value="spillage">Waste Spillage</option>
                            <option value="illegal">Illegal Dumping</option>
                            <option value="damaged">Damaged Bin</option>
                            <option value="driver-absent">Driver/Collector Absent</option>
                            <option value="vehicle">Vehicle Problem</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Location (Street/Area in Barangay)</label>
                        <input type="text" id="issue-location" required placeholder="e.g., Purok 1, near Municipal Hall">
                    </div>
                    <div class="form-group">
                        <label>Date & Time of Incident</label>
                        <input type="datetime-local" id="issue-datetime" required>
                    </div>
                    <div class="form-group">
                        <label>Priority Level</label>
                        <select id="issue-priority" required>
                            <option value="low">Low - Can wait a few days</option>
                            <option value="medium">Medium - Need attention soon</option>
                            <option value="high">High - Urgent attention needed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="issue-description" required placeholder="Describe the issue in detail"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Attach Photo (Optional)</label>
                        <input type="file" id="issue-photo" accept="image/*">
                        <small style="color: #666; display: block; margin-top: 0.5rem;">Supported formats: JPG, PNG (Max 5MB)</small>
                    </div>
                    <button type="submit" class="btn btn-warning btn-full">📤 Submit Report</button>
                </form>
            </div>

            <!-- Submitted Reports History -->
            <div class="card">
                <h3>📋 Submitted Reports</h3>
                <div class="search-filter">
                    <select id="report-status-filter" onchange="filterReports()">
                        <option value="all">All Reports</option>
                        <option value="pending">Pending</option>
                        <option value="in-review">In Review</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <button class="btn btn-info" onclick="exportReports()">📊 Export Reports</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Date Submitted</th>
                                <th>Issue Type</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="reports-history">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endsection