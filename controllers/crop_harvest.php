<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $planting_id = $_POST['planting_id'];
    $yield_qty   = floatval($_POST['yield_quantity']);
    $yield_unit  = Validator::sanitize($_POST['yield_unit']); // e.g., kg, tons
    $date        = $_POST['harvest_date']; // Actual date

    try {
        // Update status to 'harvested' and save yield
        $sql = "UPDATE plantings 
                SET status = 'harvested', 
                    yield_quantity = ?, 
                    yield_unit = ?, 
                    actual_harvest_date = ? 
                WHERE planting_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$yield_qty, $yield_unit, $date, $planting_id]);

        // Optional: Log this logic in Audit?
        // (new Audit($db))->log(Session::get('user_id'), 'HARVEST', 'plantings', $planting_id);

        Session::set('flash_success', 'Harvest recorded successfully.');

    } catch (PDOException $e) {
        Session::set('flash_error', "Database Error: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "crops.php");
    exit;
}
?>