let pickupMap;
let pickupMarkers = [];
let selectedTruckId = null;
let initialLocation = null;
let routeLine = null;

// Initialize the map when modal opens

document.querySelectorAll('.assign-route-btn').forEach(button => {
    button.addEventListener('click', function () {
        selectedTruckId = this.getAttribute('data-truck-id');
    });
});
document.getElementById('assignRouteModal').addEventListener('shown.bs.modal', async function () {

    if (!selectedTruckId) {
        alert("⚠️ Please select a truck before assigning routes.");
        const modal = bootstrap.Modal.getInstance(document.getElementById('assignRouteModal'));
        modal.hide();
        return;
    }

    // Initialize Leaflet map
    if (!pickupMap) {
        pickupMap = L.map('pickupMap').setView([13.4117, 121.1803], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(pickupMap);

        // On map click — add marker and save coordinate
        pickupMap.on('click', function (e) {
            addPickupMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    // Load truck's initial location and existing pickups
    await loadTruckData();
});

// Load truck initial location and existing pickup points
async function loadTruckData() {
    try {
        const response = await fetch(`/truck-pickups`);
        const trucksData = await response.json();

        // Find the selected truck - try matching by truck_id, id, or driver_id
        let truck = trucksData.find(t => String(t.truck_id) === String(selectedTruckId));
        
        // If not found by truck_id, try by id (database primary key)
        if (!truck) {
            truck = trucksData.find(t => String(t.id) === String(selectedTruckId));
        }
        
        // If still not found by id, try by driver_id
        if (!truck) {
            truck = trucksData.find(t => String(t.driver_id) === String(selectedTruckId));
        }

        if (!truck) {
            console.error('Available trucks:', trucksData);
            console.error('Selected value from dropdown:', selectedTruckId);
            alert("Truck is not active. Click ok to proceed anyway.");
            return;
        }

        console.log('✅ Found truck:', truck);

        // Clear existing markers and route
        clearMapLayers();

        // Set initial location
        if (truck.initial_coords && truck.initial_coords.lat && truck.initial_coords.lng) {
            initialLocation = {
                lat: truck.initial_coords.lat,
                lng: truck.initial_coords.lng
            };

            // Add initial location marker (truck icon)
            const truckIcon = L.divIcon({
                html: '🚛',
                className: 'custom-truck-icon',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            L.marker([initialLocation.lat, initialLocation.lng], { icon: truckIcon })
                .bindPopup('<strong>🚛 Initial Location</strong><br>Starting Point')
                .addTo(pickupMap);

            // Center map on initial location
            pickupMap.setView([initialLocation.lat, initialLocation.lng], 13);
        } else {
            // No initial location set - use default center (Calapan City)
            console.warn('⚠️ No initial location set for this truck. Using default center.');
            pickupMap.setView([13.4117, 121.1803], 13); // Default to Manila or your city
            initialLocation = null;
        }

        // Load existing pickup points
        if (truck.pickups && truck.pickups.length > 0) {
            truck.pickups.forEach(p => {
               addPickupMarker(p.lat, p.lng, false, p.timeWindow);
            });
        }

        // Draw route after all markers are loaded
        drawRoute();

    } catch (error) {
        console.error('❌ Error loading truck data:', error);
        alert('⚠️ Failed to load truck data.');
    }
}

// Add a pickup marker with delete button
function addPickupMarker(lat, lng, shouldDrawRoute = true, timeWindow = { start: "", end: "" }) {
    const pickupIcon = L.divIcon({
        html: '<div class="pickup-marker1">📍</div>',
        className: '',
        iconSize: [30, 30],
        iconAnchor: [0, 0],
    });

    const marker = L.marker([lat, lng], { icon: pickupIcon }).addTo(pickupMap);

    // Store pickup data with prefilled time window
    const pickupData = { 
        lat, 
        lng, 
        marker, 
        timeWindow: { start: timeWindow.start || "", end: timeWindow.end || "" }
    };
    pickupMarkers.push(pickupData);

    // Add popup with delete button + prefilled time window inputs
    marker.bindPopup(`
        <strong>📍 Pickup Point</strong><br>
        Lat: ${lat.toFixed(6)}<br>
        Lng: ${lng.toFixed(6)}<br><br>
        <label>Start Time:</label>
        <input type="time" 
               value="${pickupData.timeWindow.start}" 
               onchange="updatePickupTimeWindow(${pickupMarkers.length - 1}, 'start', this.value)" 
               class="form-control form-control-sm mb-1">
        <label>End Time:</label>
        <input type="time" 
               value="${pickupData.timeWindow.end}" 
               onchange="updatePickupTimeWindow(${pickupMarkers.length - 1}, 'end', this.value)" 
               class="form-control form-control-sm mb-2">
        <button onclick="deletePickupMarker(${pickupMarkers.length - 1})" class="btn btn-danger btn-sm">
            🗑️ Delete
        </button>
    `);

    console.log("📍 Added pickup:", { lat, lng, timeWindow: pickupData.timeWindow });

    if (shouldDrawRoute) {
        drawRoute();
    }
}


// Update time window
function updatePickupTimeWindow(index, type, value) {
    if (pickupMarkers[index]) {
        pickupMarkers[index].timeWindow[type] = value;
        console.log(`⏰ Updated pickup ${index} ${type} time:`, value);
    }
}


// Delete a pickup marker
function deletePickupMarker(index) {
    if (index >= 0 && index < pickupMarkers.length) {
        // Remove marker from map
        pickupMap.removeLayer(pickupMarkers[index].marker);
        
        // Remove from array
        pickupMarkers.splice(index, 1);
        
        console.log(`🗑️ Deleted pickup at index ${index}`);
        
        // Redraw route
        drawRoute();
        
        // Close all popups
        pickupMap.closePopup();
    }
}

// Draw route from initial location through all pickup points
async function drawRoute() {
    // Remove existing route line
    if (routeLine) {
        pickupMap.removeLayer(routeLine);
        routeLine = null;
    }

    // Skip if no initial location OR no pickups
    if (!initialLocation || pickupMarkers.length === 0) {
        console.log('⚠️ Cannot draw route: missing initial location or no pickups');
        return;
    }

    // Build coordinate array: initial location + all pickups
    const latlngs = [
        [initialLocation.lat, initialLocation.lng],
        ...pickupMarkers.map(p => [p.lat, p.lng])
    ];

    if (latlngs.length < 2) return;

    try {
        // ORS expects [lon, lat]
        const coordinates = latlngs.map(([lat, lon]) => [lon, lat]);

        const body = {
            coordinates,
            format: 'geojson'
        };

        const response = await fetch('https://api.openrouteservice.org/v2/directions/driving-car/geojson', {
            method: 'POST',
            headers: {
                'Authorization': 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjIzZGExN2U1YWNhZDRmYzhiZGRiM2JlZTQxY2JkNWNiIiwiaCI6Im11cm11cjY0In0=',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            throw new Error('Failed to fetch route');
        }

        const geojson = await response.json();

        // Draw the route line
        routeLine = L.geoJSON(geojson, {
            style: {
                color: '#3388ff',
                weight: 4,
                opacity: 0.7,
                dashArray: '10, 5'
            }
        }).bindPopup(`🚛 Truck ${selectedTruckId} Route`);

        pickupMap.addLayer(routeLine);

        console.log('✅ Route drawn successfully');

    } catch (error) {
        console.error('❌ Error drawing route:', error);
    }
}

// Clear all markers and routes except initial location
function clearMapLayers() {
    // Remove all pickup markers
    pickupMarkers.forEach(p => {
        if (p.marker) {
            pickupMap.removeLayer(p.marker);
        }
    });
    pickupMarkers = [];

    // Remove route line
    if (routeLine) {
        pickupMap.removeLayer(routeLine);
        routeLine = null;
    }

    // Remove all markers except we'll re-add initial location
    pickupMap.eachLayer(function (layer) {
        if (layer instanceof L.Marker) {
            pickupMap.removeLayer(layer);
        }
    });
}

// Reset when modal is closed
document.getElementById('assignRouteModal').addEventListener('hidden.bs.modal', function () {
    clearMapLayers();
    initialLocation = null;
});

// Save pickup points
function savePickupPoints() {
    if (!selectedTruckId) {
        alert("⚠️ Please select a truck first.");
        return;
    }

    if (pickupMarkers.length === 0) {
        alert("📍 Please add at least one pickup location.");
        return;
    }

    // Include time windows
    const pickupsToSave = pickupMarkers.map(p => ({
        lat: p.lat,
        lng: p.lng,
        timeWindow: p.timeWindow // { start: "08:00", end: "10:00" }
    }));

    fetch('/update-truck-pickups', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            truck_id: selectedTruckId,
            pickups: pickupsToSave
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Pickups successfully saved!");
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignRouteModal'));
            modal.hide();
        } else {
            alert("❌ Failed to save pickups.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("⚠️ Error saving pickups.");
    });
}


// Make deletePickupMarker globally accessible
window.deletePickupMarker = deletePickupMarker;