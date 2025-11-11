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


async function loadAllTruckMarkersAndRoutes() {
    try {
        // Fetch trucks with initial location + truck pickups
        const trucksResponse = await fetch('/truck-pickups');
        const trucksData = await trucksResponse.json();
        
        // Fetch additional pickup points
        const pickupsResponse = await fetch('/pickup-locations');
        const pickupsData = await pickupsResponse.json();

        const truckGroups = {}; // Group points by truck_id

        // Process trucks + initial location + truck pickups
        trucksData.forEach(truck => {
            const points = [];

            // Initial location → always truck icon
            if (truck.initial_coords) {
                points.push({
                    latitude: truck.initial_coords.lat,
                    longitude: truck.initial_coords.lng,
                    barangay: 'Initial Location',
                    pickup_date: '',
                    pickup_time: '',
                    driver_name: truck.driver_name,
                    icon: '🚛' // 🔹 truck icon directly
                });
            }

            // Add pickups from truck object → default pickup icon
            if (truck.pickups && truck.pickups.length > 0) {
                truck.pickups.forEach(p => {
                    points.push({
                        latitude: p.lat,
                        longitude: p.lng,
                        barangay: p.barangay ?? 'Pickup Point',
                        pickup_date: p.pickup_date ?? '',
                        pickup_time: p.pickup_time ?? '',
                        driver_name: truck.driver_name,
                        icon: '📍', // default pickup
                        timeWindow: p.timeWindow ? `${p.timeWindow.start} - ${p.timeWindow.end}` : ''
                    });
                });
            }

            truckGroups[truck.truck_id] = points;
        });

        // Merge pickups from /pickup-locations
        pickupsData.forEach(p => {
            if (!truckGroups[p.truck_id]) truckGroups[p.truck_id] = [];
            // Avoid duplicates
            const exists = truckGroups[p.truck_id].some(point =>
                point.latitude === p.latitude && point.longitude === p.longitude
            );
            if (!exists) {
                truckGroups[p.truck_id].push({
                    latitude: p.latitude,
                    longitude: p.longitude,
                    barangay: p.barangay ?? 'Pickup Point',
                    pickup_date: p.pickup_date ?? '',
                    pickup_time: p.pickup_time ?? '',
                    driver_name: p.driver_name,
                    icon: p.driver_name ? '🚛' : '📍',
                    timeWindow: p.timeWindow ? `${p.timeWindow.start} - ${p.timeWindow.end}` : ''
                });
            }
        });

        // Add markers & draw routes
        for (const truckId of Object.keys(truckGroups)) {
            const points = truckGroups[truckId];
            const truckColor = getTruckColor(truckId); // Get color for this truck

            // Initial location first, then pickups by date/time
            points.sort((a, b) => {
                if (a.icon === '🚛') return -1;
                if (b.icon === '🚛') return 1;
                
                // Sort by time window if available, otherwise by pickup_date/time
                if (a.timeWindow && b.timeWindow) {
                    const timeA = a.timeWindow.split(' - ')[0];
                    const timeB = b.timeWindow.split(' - ')[0];
                    return timeA.localeCompare(timeB);
                }
                
                const dateA = a.pickup_date ? new Date(`${a.pickup_date}T${a.pickup_time}`) : new Date(0);
                const dateB = b.pickup_date ? new Date(`${b.pickup_date}T${b.pickup_time}`) : new Date(0);
                return dateA - dateB;
            });

            // Add sequence numbers to pickup points (📍)
            let sequenceNumber = 1;
            points.forEach(p => {
                if (p.icon === '📍') {
                    p.sequence = sequenceNumber++;
                }
            });

            const latlngs = points.map(p => [p.latitude, p.longitude]);

            // Add markers
            points.forEach(p => {
                // Use sequence number for pickup points with truck color
                const iconHtml = p.icon === '📍' && p.sequence 
                    ? `<div style="background: ${truckColor}; color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${p.sequence}</div>`
                    : p.icon;

                const truckIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-truck-icon',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                });

                const marker = L.marker([p.latitude, p.longitude], { icon: truckIcon })
                    .bindPopup(`
                        <strong>${p.barangay}</strong><br>
                        🚛 Truck ID: ${truckId}<br>
                        ${p.sequence ? `📍 Stop #${p.sequence}<br>` : ''}
                        ${p.driver_name ? `👨‍✈️ Driver: ${p.driver_name}<br>` : ''}
                        ${p.timeWindow ? `⏰ ${p.timeWindow}<br>` : ''}
                        ${p.pickup_date ? `📅 ${p.pickup_date}<br>` : ''}
                        ${p.pickup_time ? `⏰ ${p.pickup_time}<br>` : ''}
                        <button onclick="openStreetView(${p.latitude}, ${p.longitude})">🛰️ Street View</button>
                    `);

                markerClusterGroup.addLayer(marker);
            });

            // Draw route connecting initial location -> pickups
            await drawRouteOnRoad(latlngs, truckId);
        }

        // Fit map to all markers
        if (markerClusterGroup.getLayers().length > 0) {
            map.fitBounds(markerClusterGroup.getBounds());
        }

    } catch (error) {
        console.error('❌ Error loading all truck markers and routes:', error);
    }
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