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
        loadPickupMarkers();
    }
});

function initializeTrackingMap() {
    console.log('🗺️ Initializing Tracking Map (Calapan City)...');

    // Clean up if map already exists
    if (map) {
        map.remove();
    }

    // ✅ Create map
    map = L.map('tracking-map', {
        center: MAP_CONFIG.center,
        zoom: MAP_CONFIG.zoom,
        minZoom: MAP_CONFIG.minZoom,
        maxZoom: MAP_CONFIG.maxZoom,
        zoomControl: true
    });

    // ✅ Add base tile layer
    L.tileLayer(TILE_LAYERS.street.url, {
        attribution: TILE_LAYERS.street.attribution
    }).addTo(map);

    // ✅ Initialize marker cluster group (empty for now)
    markerClusterGroup = L.markerClusterGroup();
    map.addLayer(markerClusterGroup);

    console.log('✅ Map centered on Calapan City, Oriental Mindoro.');
}

function loadPickupMarkers() {
    fetch('/pickup-locations')
        .then(response => response.json())
        .then(data => {
            console.log('📦 Pickup locations:', data);

            const truckGroups = {}; // Group pickups by truck_id

            data.forEach(pickup => {
                if (pickup.latitude && pickup.longitude) {

                    // Group pickups by truck ID
                    if (!truckGroups[pickup.truck_id]) {
                        truckGroups[pickup.truck_id] = [];
                    }
                    truckGroups[pickup.truck_id].push(pickup);

                    // Determine truck emoji based on status
                    let truckEmoji = '🚛';
                    if (pickup.status === 'idle') {
                        truckEmoji = '🟡';
                    }

                    const truckIcon = L.divIcon({
                        html: truckEmoji,
                        className: 'custom-truck-icon',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    });

                    const marker = L.marker([pickup.latitude, pickup.longitude], { icon: truckIcon })
                        .bindPopup(`
                            <strong>📍 ${pickup.barangay}</strong><br>
                            🚛 Truck ID: ${pickup.truck_id}<br>
                            👨‍✈️ Driver: ${pickup.driver_name}<br>
                            📅 ${pickup.pickup_date}<br>
                            ⏰ ${pickup.pickup_time}<br>
                            <button onclick="openStreetView(${pickup.latitude}, ${pickup.longitude})">🛰️ Street View</button>
                        `);

                    markerClusterGroup.addLayer(marker);
                }
            });

            // ✅ Draw paths (routes) per truck
            // ✅ Draw routes on real roads per truck
Object.keys(truckGroups).forEach(async truckId => {
    const pickups = truckGroups[truckId];

    // Sort by date + time
    pickups.sort((a, b) => {
        return new Date(`${a.pickup_date}T${a.pickup_time}`) - new Date(`${b.pickup_date}T${b.pickup_time}`);
    });

    const latlngs = pickups.map(p => [p.latitude, p.longitude]);

    // Use OpenRouteService to snap to real roads
    await drawRouteOnRoad(latlngs, truckId);
});


            // Fit map to all markers
            if (markerClusterGroup.getLayers().length > 0) {
                map.fitBounds(markerClusterGroup.getBounds());
            }
        })
        .catch(error => console.error('❌ Error loading pickup locations:', error));
}

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