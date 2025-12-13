// ✅ Declare global map variables
let map;
let markerClusterGroup;
let mapillaryViewer = null;

// ✅ Configuration (Calapan City)
const MAP_CONFIG = {
    center: [13.4117, 121.1803], // Calapan City, Oriental Mindoro
    zoom: 13,
    minZoom: 5,
    maxZoom: 18
};

const TILE_LAYERS = {
    street: {
        url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        attribution: "© OpenStreetMap contributors"
    },
    satellite: {
        url: "https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}",
        attribution: "Google Satellite",
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }
};

// ✅ Initialize map when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tracking-map')) {
        initializeTrackingMap();
        loadAllTruckMarkersAndRoutes();
    }
});
// Map Auto-Refresh Implementation - Simplified Version
let refreshInterval;

// Main refresh function that calls your existing functions
async function refreshMapData() {
    try {
        console.log('Refreshing map data...');
        
        // Call your existing functions in sequence
        await initializeTrackingMap();
        await loadAllTruckMarkersAndRoutes();
        
        // If drawRouteOnRoad needs specific parameters, adjust accordingly
        // Example: await drawRouteOnRoad(latLngs, truckId);
        
        console.log('Map data refreshed successfully');
    } catch (error) {
        console.error('Error refreshing map data:', error);
    }
}

// Function to start auto-refresh
function startAutoRefresh(interval) {
    // Clear existing interval if any
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }

    // Set new interval
    refreshInterval = setInterval(() => {
        refreshMapData();
    }, interval);

    console.log(`Auto-refresh started: every ${interval/1000} seconds`);
}

// Function to stop auto-refresh
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
        console.log('Auto-refresh stopped');
    }
}

// Event listener for the select dropdown
document.addEventListener('DOMContentLoaded', () => {
    const updateIntervalSelect = document.getElementById('update-interval');
    
    if (updateIntervalSelect) {
        // Listen for changes in the dropdown
        updateIntervalSelect.addEventListener('change', (e) => {
            const interval = parseInt(e.target.value);
            startAutoRefresh(interval);
        });

        // Optional: Start auto-refresh with default value on page load
        // Uncomment the next line if you want it to start automatically
        // startAutoRefresh(parseInt(updateIntervalSelect.value));
    }



    // Stop refresh when page is hidden (battery saving)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && refreshInterval) {
            stopAutoRefresh();
        } else if (!document.hidden && updateIntervalSelect) {
            const interval = parseInt(updateIntervalSelect.value);
            startAutoRefresh(interval);
        }
    });
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopAutoRefresh();
});

// Optional: Export functions if using modules
// export { refreshMapData, startAutoRefresh, stopAutoRefresh };
function initializeTrackingMap() {
    console.log('🗺️ Initializing Tracking Map (Calapan City)...');

    // Only initialize map once
    if (!map) {
        // ✅ Create map (first time only)
        map = L.map('tracking-map', {
            center: MAP_CONFIG.center,
            zoom: MAP_CONFIG.zoom,
            minZoom: MAP_CONFIG.minZoom,
            maxZoom: MAP_CONFIG.maxZoom,
            zoomControl: true,
            zoomAnimation: false,  // Disable zoom animation
            fadeAnimation: false,  // Disable fade animation
            markerZoomAnimation: false  // Disable marker zoom animation
        });

        // ✅ Add base tile layer
        L.tileLayer(TILE_LAYERS.street.url, {
            attribution: TILE_LAYERS.street.attribution
        }).addTo(map);

        console.log('✅ Map centered on Calapan City, Oriental Mindoro.');
    }

    // ✅ Always clear and reinitialize marker cluster group on refresh
    if (markerClusterGroup) {
        map.removeLayer(markerClusterGroup);
    }
    markerClusterGroup = L.markerClusterGroup();
    map.addLayer(markerClusterGroup);

    console.log('✅ Markers cleared and ready for new data.');
}
async function loadAllTruckMarkersAndRoutes(filterDate = null) {
    try {
        // ---- DATE SETUP ----
        // Use provided filterDate or default to today
        const targetDate = filterDate || (() => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        })();

        console.log('🗓️ Target Date:', targetDate);

        // ---- FETCH DATA ----
        const [trucksResponse, pickupsResponse] = await Promise.all([
            fetch('/truck-pickups'),
            fetch('/pickup-locations')
        ]);

        let trucksData = await trucksResponse.json();
        let pickupsData = await pickupsResponse.json();

        console.log('📦 Raw Trucks Data:', trucksData);
        console.log('📦 Raw Pickups Data:', pickupsData);

        // ---- FILTER BY TARGET DATE ----
        trucksData = trucksData.filter(truck => truck.pickup_date === targetDate);
        
        // Get current truck locations from pickup-locations (for plotting truck icons)
        const currentTruckLocations = pickupsData.filter(p => p.pickup_date === targetDate);

        console.log('✅ Filtered Trucks (date match):', trucksData);
        console.log('✅ Current Truck Locations:', currentTruckLocations);

        // ---- PROCESS TRUCKS ----
        const truckGroups = {};

        // First, plot all trucks at their current locations from /pickup-locations
        currentTruckLocations.forEach(location => {
            const truck = trucksData.find(t => t.truck_id === location.truck_id);
            
            if (!truck) return; // Skip if truck data not found

            if (!truckGroups[location.truck_id]) {
                truckGroups[location.truck_id] = [];
            }

            // Determine icon based on status
            let truckIcon = '🚛'; // Default for active
            if (location.status === 'idle') {
                truckIcon = '🟡';
            } else if (location.status === 'maintenance') {
                truckIcon = '🔧';
            }

            // Add truck's current location
            truckGroups[location.truck_id].push({
                latitude: parseFloat(location.latitude),
                longitude: parseFloat(location.longitude),
                barangay: location.barangay,
                pickup_date: location.pickup_date,
                pickup_time: location.pickup_time,
                driver_name: location.driver_name,
                icon: truckIcon,
                isTruckLocation: true,
                status: location.status
            });
        });

        // Now add pickup routes for ACTIVE trucks only
        trucksData.forEach(truck => {
            if (truck.status !== 'active') return; // Skip non-active trucks
            
            if (!truckGroups[truck.truck_id]) {
                truckGroups[truck.truck_id] = [];
            }

            // Add pickups from truck.pickups array
            if (truck.pickups && truck.pickups.length > 0) {
                truck.pickups.forEach(p => {
                    // Check if this pickup point is not the same as truck's current location
                    const isDuplicate = truckGroups[truck.truck_id].some(point =>
                        Math.abs(point.latitude - p.lat) < 0.0001 &&
                        Math.abs(point.longitude - p.lng) < 0.0001
                    );

                    if (!isDuplicate) {
                        truckGroups[truck.truck_id].push({
                            latitude: p.lat,
                            longitude: p.lng,
                            barangay: 'Pickup Point',
                            pickup_date: truck.pickup_date,
                            pickup_time: '',
                            driver_name: truck.driver_name,
                            icon: '📍',
                            timeWindow: p.timeWindow
                                ? `${p.timeWindow.start ?? ''} - ${p.timeWindow.end ?? ''}`
                                : '',
                            isPickupPoint: true
                        });
                    }
                });
            }
        });

        console.log('🗺️ Final Truck Groups:', truckGroups);

        // ---- RENDER MAP ----
        for (const truckId of Object.keys(truckGroups)) {
            const points = truckGroups[truckId];
            if (points.length === 0) continue;

            const truckColor = getTruckColor(truckId);
            
            // Find truck's current location and status
            const truckLocation = points.find(p => p.isTruckLocation);
            const isActive = truckLocation && truckLocation.status === 'active';

            if (isActive) {
                // ACTIVE TRUCK: Sort points, assign sequences, draw route
                
                // Sort: truck location first, then by time window
                points.sort((a, b) => {
                    if (a.isTruckLocation) return -1;
                    if (b.isTruckLocation) return 1;

                    if (a.timeWindow && b.timeWindow) {
                        return a.timeWindow.localeCompare(b.timeWindow);
                    }
                    return 0;
                });

                // Assign sequence numbers to pickup points only
                let seq = 1;
                points.forEach(p => {
                    if (p.isPickupPoint) {
                        p.sequence = seq++;
                    }
                });

                // Render all markers
                points.forEach(p => {
                    let iconHtml;
                    
                    if (p.isTruckLocation) {
                        // Truck icon
                        iconHtml = p.icon;
                    } else if (p.isPickupPoint) {
                        // Numbered pickup point
                        iconHtml = `<div style="background:${truckColor};color:white;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-weight:bold;">${p.sequence}</div>`;
                    }

                    const marker = L.marker(
                        [p.latitude, p.longitude],
                        {
                            icon: L.divIcon({
                                html: iconHtml,
                                className: 'custom-truck-icon',
                                iconSize: [30, 30],
                                iconAnchor: [15, 30]
                            })
                        }
                    ).bindPopup(`
                        <strong>${p.barangay}</strong><br>
                        🚛 Truck ID: ${truckId}<br>
                        ${p.sequence ? `📍 Stop #${p.sequence}<br>` : ''}
                        ${p.isTruckLocation ? '📍 Current Location<br>' : ''}
                        ${p.driver_name ? `👨‍✈️ ${p.driver_name}<br>` : ''}
                        ${p.timeWindow ? `⏰ ${p.timeWindow}<br>` : ''}
                        ${p.pickup_time ? `⏰ ${p.pickup_time}<br>` : ''}
                        📅 ${targetDate}
                    `);

                    markerClusterGroup.addLayer(marker);
                });

                // Draw route if there are multiple points
                if (points.length > 1) {
                    const latlngs = points.map(p => [p.latitude, p.longitude]);
                    await drawRouteOnRoad(latlngs, truckId);
                }

            } else {
                // IDLE/MAINTENANCE TRUCK: Show only status icon, no route
                
                points.forEach(p => {
                    if (!p.isTruckLocation) return; // Only show truck location
                    
                    const marker = L.marker(
                        [p.latitude, p.longitude],
                        {
                            icon: L.divIcon({
                                html: p.icon,
                                className: 'custom-truck-icon',
                                iconSize: [30, 30],
                                iconAnchor: [15, 30]
                            })
                        }
                    ).bindPopup(`
                        <strong>${p.barangay}</strong><br>
                        🚛 Truck ID: ${truckId}<br>
                        👨‍✈️ ${p.driver_name}<br>
                        📅 ${p.pickup_date}<br>
                        ⏰ ${p.pickup_time}<br>
                        <em>Status: ${p.icon === '🟡' ? 'Idle' : 'Maintenance'}</em>
                    `);

                    markerClusterGroup.addLayer(marker);
                });
            }
        }

        // Fit map bounds
        if (markerClusterGroup.getLayers().length > 0) {
            map.fitBounds(markerClusterGroup.getBounds());
        } else {
            console.log('ℹ️ No markers to display for date:', targetDate);
        }

    } catch (error) {
        console.error('❌ Error loading map:', error);
    }
}

// Usage examples:
// loadAllTruckMarkersAndRoutes(); // Uses today's date
// loadAllTruckMarkersAndRoutes('2025-12-15'); // Uses custom date (ready for future UI integration)

async function drawRouteOnRoad(latlngs, truckId) {
    if (latlngs.length < 2) return;

    const coordinates = latlngs.map(([lat, lon]) => [lon, lat]); // ORS expects [lon, lat]

    const body = {
        coordinates,
        format: 'geojson'
    };

    const response = await fetch('https://api.openrouteservice.org/v2/directions/driving-car/geojson', {
        method: 'POST',
        headers: {
            'Authorization': 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjIzZGExN2U1YWNhZDRmYzhiZGRiM2JlZTQxY2JkNWNiIiwiaCI6Im11cm11cjY0In0=', // 🔑 Replace this with your actual key
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(body)
    });

    const geojson = await response.json();

    // Draw the route line
    const routeLine = L.geoJSON(geojson, {
        style: {
            color: getTruckColor(truckId),
            weight: 5,
            opacity: 0.8
        }
    }).bindPopup(`🚛 Truck ${truckId} Route`);

    map.addLayer(routeLine);
}


// Optional helper to assign a unique color per truck
function getTruckColor(truckId) {
    const colors = ['#1E90FF', '#FF6347', '#32CD32', '#FFD700', '#8A2BE2', '#FF69B4'];
    return colors[truckId % colors.length];
}


function openStreetView(lat, lng) {
    const viewContainer = document.getElementById('street-view');
    
    if (!viewContainer) {
        console.error("❌ Street view container not found");
        return;
    }

    viewContainer.style.display = 'block';
    viewContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #000; color: #fff;">Loading street view...</div>';

    if (typeof mapillary === "undefined") {
        console.error("❌ Mapillary is not loaded.");
        viewContainer.innerHTML = '<div style="padding: 20px; background: #000; color: #fff;">Mapillary library not loaded.</div>';
        return;
    }

    try {
        // ✅ Destroy existing viewer before creating new one
        if (mapillaryViewer) {
            mapillaryViewer.remove();
            mapillaryViewer = null;
        }

        // Clear the container
        viewContainer.innerHTML = '';

        // ✅ Initialize Mapillary Viewer (v4 API)
        const { Viewer } = mapillary;
        
        mapillaryViewer = new Viewer({
            container: 'street-view',
            accessToken: 'MLY|24792141693789156|5c78797fb9f315cbe85b74f647b82da0',
            component: {
                cover: false,
                sequence: false
            }
        });

        console.log('✅ Mapillary viewer initialized');

        // ✅ Search for nearby images first using Data API
        const radius = 100; // Search within 100 meters
        const apiUrl = `https://graph.mapillary.com/images?access_token=MLY|24792141693789156|5c78797fb9f315cbe85b74f647b82da0&fields=id,computed_geometry&bbox=${lng-0.001},${lat-0.001},${lng+0.001},${lat+0.001}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    // Found images nearby - move to the first one
                    const imageId = data.data[0].id;
                    console.log('✅ Found nearby image:', imageId);
                    
                    mapillaryViewer.moveTo(imageId)
                        .catch(error => {
                            console.error('❌ Error moving to image:', error);
                            showNoImageryMessage();
                        });
                } else {
                    console.warn('⚠️ No Mapillary imagery found near this location');
                    showNoImageryMessage();
                }
            })
            .catch(error => {
                console.error('❌ Error fetching nearby images:', error);
                showNoImageryMessage();
            });

    } catch (error) {
        console.error('❌ Mapillary error:', error);
        showNoImageryMessage();
    }
}

function showNoImageryMessage() {
    const viewContainer = document.getElementById('street-view');
    if (viewContainer) {
        viewContainer.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #1a1a1a; color: #fff; padding: 20px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 20px;">🗺️</div>
                <h3 style="margin: 0 0 10px 0;">No Street View Available</h3>
                <p style="margin: 0; color: #aaa;">Mapillary doesn't have street-level imagery for this location yet.</p>
                <button onclick="closeStreetView()" style="margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">Close</button>
            </div>
        `;
    }
}

// ✅ Function to close street view
function closeStreetView() {
    const viewContainer = document.getElementById('street-view');
    if (viewContainer) {
        viewContainer.style.display = 'none';
        viewContainer.innerHTML = '';
    }
    
    if (mapillaryViewer) {
        mapillaryViewer.remove();
        mapillaryViewer = null;
    }
}

document.getElementById('driver-status-select').addEventListener('change', async function() {
    const selected = this.value;
    const pickupDropdown = document.getElementById('pickup-location-select');

    if (selected === 'at-pickup') {
        pickupDropdown.style.display = 'block';
        pickupDropdown.innerHTML = '<option disabled selected>Loading pickup locations...</option>';

        try {
            const response = await fetch('/driver/pickup-locations');
            const data = await response.json();

            console.log("✅ Loaded pickup locations: ", data);
            pickupDropdown.innerHTML = ''; // clear old options

            if (data.length > 0) {
                for (const loc of data) {
                    const { latitude, longitude, timeWindow } = loc;

                    // Reverse geocode each point
                    const geoResponse = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&zoom=18&addressdetails=1`
                    );
                    const geoData = await geoResponse.json();
                    const readableAddress = geoData.display_name || `${latitude}, ${longitude}`;

                    const opt = document.createElement('option');
                    opt.value = `${latitude},${longitude}`;
                    opt.textContent = readableAddress + (timeWindow ? ` (${timeWindow.start} - ${timeWindow.end})` : '');
                    pickupDropdown.appendChild(opt);
                }
            } else {
                pickupDropdown.innerHTML = '<option>No pickup points found</option>';
            }
        } catch (err) {
            console.error('Error loading pickup locations:', err);
            pickupDropdown.innerHTML = '<option>Error loading data</option>';
        }
    } else {
        pickupDropdown.style.display = 'none';
    }
});



document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('driver-routes');
    const filterInput = document.getElementById('filter-date');

    // Helper to normalize date to YYYY-MM-DD
    const normalizeDate = (dateStr) => {
        return new Date(dateStr).toISOString().split('T')[0];
    };

    const renderPickups = (filterDate) => {
        tbody.innerHTML = ''; // Clear table
        const pickups = window.scheduledPickups || [];

        // Filter pickups for the selected date
        const filteredPickups = pickups.filter(p => normalizeDate(p.pickup_date) === filterDate);

        if (filteredPickups.length === 0) {
            // Find next upcoming pickup
            const futurePickups = pickups
                .filter(p => normalizeDate(p.pickup_date) > filterDate)
                .map(p => normalizeDate(p.pickup_date))
                .sort();
            const nextPickup = futurePickups.length > 0 ? futurePickups[0] : null;

            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="4" class="text-center">
                                No pickups today (${filterDate})
                                ${nextPickup ? ` | Next pickup: ${nextPickup}` : ''}
                            </td>`;
            tbody.appendChild(tr);
            return;
        }

        const today = new Date().toISOString().split('T')[0];

        // Render filtered pickups
        filteredPickups.forEach(pickup => {
            if (!pickup.points || pickup.points.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="4" class="text-center">No pickup locations found for this truck.</td>`;
                tbody.appendChild(tr);
                return;
            }

            // Parse completed_routes safely
            let completedRoutes = [];
            if (pickup.completed_routes) {
                try {
                    completedRoutes = Array.isArray(pickup.completed_routes)
                        ? pickup.completed_routes
                        : JSON.parse(pickup.completed_routes);
                } catch (err) {
                    completedRoutes = [];
                }
            }

            pickup.points.forEach((point, index) => {
                const rowId = `${pickup.id}-${index}`;

                const tr = document.createElement('tr');
                tr.dataset.pickupId = pickup.id;
                tr.dataset.lat = point.lat;
                tr.dataset.lng = point.lng;

                // Date column
                const tdDate = document.createElement('td');
                tdDate.classList.add('text-center');
                tdDate.textContent = new Date(pickup.pickup_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                tr.appendChild(tdDate);

                // Address column
                const tdAddress = document.createElement('td');
                tdAddress.classList.add('text-start');
                tdAddress.innerHTML = `<span id="short-address-${rowId}">Loading address...</span>
                                       <span id="full-address-${rowId}" class="d-none"></span>`;
                tr.appendChild(tdAddress);

                // Status column
                const tdStatus = document.createElement('td');
                tdStatus.classList.add('text-center');
                const isCompleted = completedRoutes.some(route => {
                    const tolerance = 0.00001;
                    return Math.abs(route.lat - point.lat) < tolerance && Math.abs(route.lng - point.lng) < tolerance;
                });
                const statusText = isCompleted ? 'Completed' : 'Pending';
                const statusClass = isCompleted ? 'success' : 'secondary';
                tdStatus.innerHTML = `<span class="badge bg-${statusClass}">${statusText}</span>`;
                tr.appendChild(tdStatus);

                // Action column
                const tdAction = document.createElement('td');
                tdAction.classList.add('text-center');
                const btn = document.createElement('button');
                btn.classList.add('btn', 'btn-sm', 'btn-primary', 'mark-done-btn');
                btn.textContent = 'Mark As Done';

                // Disable if completed or pickup is not today
                if (isCompleted || normalizeDate(pickup.pickup_date) !== today) {
                    btn.disabled = true;
                }
                tdAction.appendChild(btn);
                tr.appendChild(tdAction);

                tbody.appendChild(tr);

                // Fetch readable address
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${point.lat}&lon=${point.lng}&zoom=18`)
                    .then(res => res.json())
                    .then(data => {
                        const fullAddress = data.display_name || `${point.lat}, ${point.lng}`;
                        const shortAddress = fullAddress.length > 40 ? fullAddress.slice(0, 40) : fullAddress;
                        const isTruncated = fullAddress.length > 40;

                        const shortSpan = document.getElementById(`short-address-${rowId}`);
                        const fullSpan = document.getElementById(`full-address-${rowId}`);

                        shortSpan.innerHTML = shortAddress + (isTruncated ? ` <span class="text-primary fw-bold" style="cursor:pointer;" onclick="toggleFullAddress('${rowId}')">...</span>` : '');
                        fullSpan.innerHTML = fullAddress + (isTruncated ? ` <span class="text-danger fw-bold" style="cursor:pointer;" onclick="toggleFullAddress('${rowId}', true)">⤴️</span>` : '');
                    })
                    .catch(() => {
                        const shortSpan = document.getElementById(`short-address-${rowId}`);
                        shortSpan.textContent = `${point.lat}, ${point.lng} (Unknown location)`;
                    });

                // Mark As Done click
                btn.addEventListener('click', async () => {
                    try {
                        const res = await fetch(`/pickup/${pickup.id}/complete-point`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ lat: point.lat, lng: point.lng })
                        });
                        const data = await res.json();
                        if (data.success) {
                            tdStatus.querySelector('span.badge').textContent = 'Completed';
                            tdStatus.querySelector('span.badge').classList.remove('bg-secondary');
                            tdStatus.querySelector('span.badge').classList.add('bg-success');
                            btn.disabled = true;

                            if (!pickup.completed_routes) pickup.completed_routes = [];
                            pickup.completed_routes.push({ lat: point.lat, lng: point.lng });
                        }
                    } catch (err) {
                        console.error(err);
                    }
                });

            });
        });
    };

    // Initial render with today's date
    renderPickups(filterInput.value);

    // Re-render on date change
    filterInput.addEventListener('change', () => {
        renderPickups(filterInput.value);
    });
});










// Toggle function (same as your Blade one)
function toggleFullAddress(rowId, hide = false) {
    const shortSpan = document.getElementById(`short-address-${rowId}`);
    const fullSpan = document.getElementById(`full-address-${rowId}`);
    if (hide) {
        shortSpan.classList.remove('d-none');
        fullSpan.classList.add('d-none');
    } else {
        shortSpan.classList.add('d-none');
        fullSpan.classList.remove('d-none');
    }
}






