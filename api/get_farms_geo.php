<?php
// api/get_farms_geo.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

header('Content-Type: application/json');

try {
    // 1. Auth Check (Optional: API Key or Session)
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized");
    }

    // 2. Fetch Data
    $db = Database::getInstance()->getConnection();
    
    // We select specific columns to keep the payload light
    $sql = "SELECT farm_id, farm_name, latitude, longitude, boundary_polygon, status, area_total_sqm 
            FROM farms 
            WHERE status = 'active'";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $farms = $stmt->fetchAll();

    // 3. Format Data
    $features = [];
    foreach ($farms as $farm) {
        // Decode the GeoJSON polygon string stored in DB
        $polygon = !empty($farm['boundary_polygon']) ? json_decode($farm['boundary_polygon']) : null;

        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'id' => $farm['farm_id'],
                'name' => $farm['farm_name'],
                'area' => $farm['area_total_sqm'],
                'status' => $farm['status'],
                'url' => BASE_URL . "views/farms/show.php?id=" . $farm['farm_id']
            ],
            'geometry' => $polygon, // Polygon data
            'location' => [         // Marker data
                'lat' => $farm['latitude'],
                'lng' => $farm['longitude']
            ]
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $features]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>