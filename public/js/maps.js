/**
 * public/js/maps.js
 * Requires Leaflet.js to be loaded first
 */

let mapInstance = null;

function initMap(elementId, lat = 20.5937, lng = 78.9629, zoom = 5) {
    if (!document.getElementById(elementId)) return;

    mapInstance = L.map(elementId).setView([lat, lng], zoom);

    // Add OpenStreetMap Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(mapInstance);

    return mapInstance;
}

function loadFarmMarkers(apiUrl) {
    if (!mapInstance) return;

    fetch(apiUrl)
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const bounds = L.latLngBounds();
                
                response.data.forEach(farm => {
                    // Add Marker
                    if (farm.location.lat && farm.location.lng) {
                        const marker = L.marker([farm.location.lat, farm.location.lng])
                            .bindPopup(`<b>${farm.properties.name}</b><br><a href="${farm.properties.url}">View Details</a>`)
                            .addTo(mapInstance);
                        bounds.extend(marker.getLatLng());
                    }

                    // Add Polygon (if exists)
                    if (farm.geometry) {
                        const poly = L.geoJSON(farm.geometry, {
                            style: { color: "#10b981", weight: 2, fillOpacity: 0.2 }
                        }).addTo(mapInstance);
                        bounds.extend(poly.getBounds());
                    }
                });

                if (response.data.length > 0) {
                    mapInstance.fitBounds(bounds, { padding: [50, 50] });
                }
            }
        })
        .catch(err => console.error("Map Data Error:", err));
}