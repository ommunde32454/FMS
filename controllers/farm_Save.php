<?php
// controllers/farm_save.php (Handles BOTH Create and Update)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $farm_id = $_POST['farm_id'] ?? null; // Check if an ID exists (for UPDATE)

    // Common Data
    $data = [
        'owner_id'      => $_POST['owner_id'],
        'farm_name'     => Validator::sanitize($_POST['farm_name']),
        'survey_number' => Validator::sanitize($_POST['survey_number']),
        'area'          => floatval($_POST['area']),
        'lat'           => !empty($_POST['lat']) ? floatval($_POST['lat']) : null,
        'lng'           => !empty($_POST['lng']) ? floatval($_POST['lng']) : null,
        'polygon'       => $_POST['boundary_polygon'] ?? null 
    ];

    if (empty($data['farm_name']) || empty($data['owner_id'])) {
        Session::set('flash_error', 'Farm Name and Owner are required.');
        header("Location: " . BASE_URL . "farms.php");
        exit;
    }

    $farmModel = new Farm($db);
    $audit = new Audit($db);
    $currentUserId = Session::get('user_id');

    try {
        if ($farm_id) {
            // --- ACTION: UPDATE EXISTING FARM ---
            $sql = "UPDATE farms SET owner_id=?, farm_name=?, survey_number=?, area_total_sqm=?, latitude=?, longitude=?, boundary_polygon=?, updated_at=NOW() WHERE farm_id=?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $data['owner_id'], $data['farm_name'], $data['survey_number'], $data['area'], $data['lat'], $data['lng'], $data['polygon'], $farm_id
            ]);

            // Audit Log
            $audit->log($currentUserId, 'UPDATE', 'farms', $farm_id, null, $data);
            
            Session::set('flash_success', 'Farm updated successfully.');
            $redirectId = $farm_id;
            
        } else {
            // --- ACTION: CREATE NEW FARM ---
            $redirectId = $farmModel->create($data);

            // Audit Log
            $audit->log($currentUserId, 'CREATE', 'farms', $redirectId, null, $data);
            
            Session::set('flash_success', 'Farm created successfully.');
        }

        // Redirect to the details page of the saved/updated farm
        header("Location: " . BASE_URL . "farm_details.php?id=" . $redirectId);

    } catch (Exception $e) {
        Session::set('flash_error', 'Error processing farm: ' . $e->getMessage());
        header("Location: " . BASE_URL . "farms.php");
    }
    exit;
}
?>