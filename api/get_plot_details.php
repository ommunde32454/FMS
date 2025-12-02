<?php
// api/get_plot_details.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

header('Content-Type: application/json');

try {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception("Unauthorized");

    $plotId = $_GET['id'] ?? null;
    if (!$plotId) throw new Exception("Plot ID required");

    $db = Database::getInstance()->getConnection();
    
    // Get Plot + Parent Farm Name
    $sql = "SELECT p.*, f.farm_name 
            FROM plots p 
            JOIN farms f ON p.farm_id = f.farm_id 
            WHERE p.plot_id = ?";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$plotId]);
    $plot = $stmt->fetch();

    if (!$plot) throw new Exception("Plot not found");

    echo json_encode(['status' => 'success', 'data' => $plot]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>