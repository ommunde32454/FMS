<?php
// api/search_global.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

header('Content-Type: application/json');

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception("Unauthorized");

    $query = $_GET['q'] ?? '';
    if (strlen($query) < 2) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    $db = Database::getInstance()->getConnection();
    $term = "%$query%";
    $results = [];

    // 1. Search Farms
    $stmt = $db->prepare("SELECT farm_id as id, farm_name as title, 'farm' as type FROM farms WHERE farm_name LIKE ? LIMIT 5");
    $stmt->execute([$term]);
    $results = array_merge($results, $stmt->fetchAll());

    // 2. Search Owners
    $stmt = $db->prepare("SELECT owner_id as id, display_name as title, 'owner' as type FROM owners WHERE display_name LIKE ? LIMIT 3");
    $stmt->execute([$term]);
    $results = array_merge($results, $stmt->fetchAll());

    // 3. Search Items
    $stmt = $db->prepare("SELECT inv_id as id, item_name as title, 'item' as type FROM inventory_items WHERE item_name LIKE ? LIMIT 3");
    $stmt->execute([$term]);
    $results = array_merge($results, $stmt->fetchAll());

    echo json_encode(['status' => 'success', 'data' => $results]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>