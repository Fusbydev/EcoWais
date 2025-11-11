    @extends ('layouts.app')

    @section ('content')
    <div id="tracking-page" class="page">
            <div class="container">
                <div class="tracking-header">
                    <h1>🚛 Truck Monitoring</h1>
                    <p>GPS monitoring with OpenStreetMap integration</p>
                </div>

                <!-- Enhanced Tracking Controls -->
                <div class="tracking-controls">
                    <div class="control-group">
                        <label>🔍 Filter Trucks:</label>
                        <select id="truck-filter">
                            <option value="all">All Trucks</option>
                            <option value="active">Active Only</option>
                            <option value="idle">Idle Trucks</option>
                            <option value="maintenance">Under Maintenance</option>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label>📍 View Mode:</label>
                        <select id="view-mode">
                            <option value="satellite">Satellite View</option>
                            <option value="street">Street View</option>
                            <option value="terrain">Terrain View</option>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label>⏱️ Update Interval:</label>
                        <select id="update-interval">
                            <option value="5000">5 seconds</option>
                            <option value="10000">10 seconds</option>
                            <option value="30000">30 seconds</option>
                            <option value="60000">1 minute</option>
                        </select>
                    </div>
                    
                    <button class="btn btn-success" onclick="toggleAutoRefresh()">
                        <span id="refresh-status">🔄 Auto Refresh: ON</span>
                    </button>
                    
                    <button class="btn btn-warning" onclick="centerMapOnFleet()">
                        📍 Center on Fleet
                    </button>
                    
                    <button class="btn btn-info" onclick="toggleTrafficLayer()">
                        🚦 Toggle Traffic
                    </button>
                </div>

                <!-- Map Container with Enhanced Features -->
                <div class="map-container">
                    <div id="tracking-map"></div>
                    <div id="street-view" style="width: 100%; height: 500px; display: none;"></div>
                    <!-- Map Legend -->
                    <div class="map-legend">
                        <h4>🗺️ Legend</h4>
                        <div class="legend-item">
                            <span class="legend-marker active">🚛</span> Active Trucks
                        </div>
                        <div class="legend-item">
                            <span class="legend-marker idle">🟡</span> Idle Trucks
                        </div>
                        <div class="legend-item">
                            <span class="legend-marker maintenance">🔧</span> Maintenance
                        </div>
                        <div class="legend-item">
                            <span class="legend-marker depot">🏢</span> Depot/Base
                        </div>
                        <div class="legend-item">
                            <span class="legend-marker route">📍</span> Pickup Points
                        </div>
                    </div>
                    
                    <!-- Map Info Panel -->
                    <div class="map-info-panel" id="map-info-panel">
                        <h4>📊 Fleet Overview</h4>
                        <div id="fleet-stats">
                            <div class="stat-item">
                                <span class="stat-label">Active:</span>
                                <span class="stat-value" id="active-count">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Idle:</span>
                                <span class="stat-value" id="idle-count">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total Distance:</span>
                                <span class="stat-value" id="total-distance">0 km</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Truck Status Table -->
                <div class="card">
                    <div class="card-header">
                        <h3>🚛 Active Fleet Status</h3>
                        <div class="search-filter">
                            <input type="text" id="truck-search" placeholder="Search trucks or drivers...">
                            <button class="btn btn-info" onclick="exportFleetData()">📊 Export Data</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-enhanced">
                            <thead>
                                <tr>
                                    <th>🆔 Truck ID</th>
                                    <th>👤 Driver</th>
                                    <th>📍 Current Location</th>
                                    <th>🛣️ Route</th>
                                    <th>📈 Progress</th>
                                    <th>⏰ ETA</th>
                                    <th>⛽ Fuel</th>
                                    <th>🔄 Status</th>
                                    <th>🎛️ Actions</th>
                                </tr>
                            </thead>
                            <tbody id="truck-status-table"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Route Planning Panel -->
                <div class="card">
                    <div class="card-header">
                        <h3>🗺️ Route Planning & Optimization</h3>
                    </div>
                    <div class="route-planning-panel">
                        <div class="form-group">
                            <label>Select Truck for Route Planning:</label>
                           <select class="form-control" id="truck" name="truck" required>
                            <option value="">Select Truck</option>
                            @foreach($trucks as $truck)
                                <option value="{{ $truck->id }}">{{ $truck->truck_id }}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="route-actions">
                            <button class="btn btn-success" onclick="optimizeRoute()">🔄 Optimize Route</button>
                            <button class="btn btn-info" onclick="showRouteDetails()">📋 Route Details</button>
                             <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#assignRouteModal">
                                📍 Assign New Route
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         <!-- Assign New Route Modal -->
<div class="modal fade" id="assignRouteModal" tabindex="-1" aria-labelledby="assignRouteLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="assignRouteLabel">📍 Assign New Route to Truck #SAD</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="text-muted">
          Select new pickup locations from a map.
        </p>

        <!-- Map container -->
        <div id="pickupMap" style="height: 500px; border-radius: 8px;"></div>
      </div>

      <div class="modal-footer">
    <button type="button" class="btn btn-primary" onclick="savePickupPoints()">💾 Save Pickups</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

    </div>
  </div>
</div>


@endsection 