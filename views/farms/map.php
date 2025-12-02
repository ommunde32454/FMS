<?php
// views/farms/map.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$ownerId = $_GET['owner'] ?? null; // Check for owner filter in URL
$isOwnerView = !empty($ownerId);

// Pass the ownerId filter down to the JS via a PHP variable
?>

<div class="h-[calc(100vh-150px)] flex flex-col">
    <div class="flex justify-between items-center mb-2 px-2">
        <h1 class="text-2xl font-bold">
            <i class="fas fa-map-marked-alt mr-2"></i> 
            <?php echo $isOwnerView ? 'My Land Visualization' : 'Global Farm Map'; ?>
        </h1>
        <div class="text-sm bg-white px-3 py-1 rounded shadow">
            <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mr-2"></span> Farm Location
        </div>
    </div>
    
    <!-- Map Container -->
    <div id="farmMap" class="flex-grow w-full rounded shadow border"></div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('farmMap').setView([20.5937, 78.9629], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // CRITICAL FIX: Add owner filter to API call if present
    const ownerFilter = '<?php echo $ownerId; ?>';
    let apiUrl = '<?php echo BASE_URL; ?>api.php?action=get_farms';
    if (ownerFilter) {
        apiUrl += `&owner_id=${ownerFilter}`;
        // Zoom in more aggressively for a single owner's farms
        map.setZoom(10); 
    }

    // Fetch data from API
    fetch(apiUrl)
        .then(res => res.json())
        .then(response => {
            if(response.status === 'success') {
                var bounds = L.latLngBounds();
                response.data.forEach(farm => {
                    if(farm.location.lat && farm.location.lng) {
                        var m = L.marker([farm.location.lat, farm.location.lng])
                            .bindPopup(`<b>${farm.properties.name}</b><br><a href="${farm.properties.url}">View</a>`)
                            .addTo(map);
                        bounds.extend(m.getLatLng());
                    }
                    if (farm.geometry) {
                        const poly = L.geoJSON(farm.geometry, {
                            style: { color: "#10b981", weight: 2, fillOpacity: 0.2 }
                        }).addTo(map);
                        bounds.extend(poly.getBounds());
                    }
                });

                if(response.data.length > 0 && !ownerFilter) map.fitBounds(bounds);
            }
        });
});
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>