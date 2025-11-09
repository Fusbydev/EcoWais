// ✅ Declare global map variables
let map;
let markerClusterGroup;

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

            data.forEach(pickup => {
                if (pickup.latitude && pickup.longitude) {

                    // ✅ Determine truck emoji based on status
                    let truckEmoji = '🚛'; // default
                    if (pickup.status === 'idle') {
                        truckEmoji = '🟡';
                    }

                    // ✅ Create a custom truck marker using a DivIcon
                    const truckIcon = L.divIcon({
                        html: truckEmoji,
                        className: 'custom-truck-icon',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    });

                    const marker = L.marker([pickup.latitude, pickup.longitude], { icon: truckIcon })
                        .bindPopup(`
                            <strong>📍 ${pickup.barangay}</strong><br>
                            ${truckEmoji} Truck ID: ${pickup.truck_id}<br>
                            👨‍✈️ Driver: ${pickup.driver_name}<br>
                            📅 ${pickup.pickup_date}<br>
                            ⏰ ${pickup.pickup_time}
                        `);

                    markerClusterGroup.addLayer(marker);
                }
            });

            if (markerClusterGroup.getLayers().length > 0) {
                map.fitBounds(markerClusterGroup.getBounds());
            }
        })
        .catch(error => console.error('❌ Error loading pickup locations:', error));
}


