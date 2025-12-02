<?php
// controllers/assignment_save.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();
(new Auth($db))->requireRole(['superadmin', 'manager']); // Only management can assign

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    try {
        $assignmentModel = new Assignment($db);
        
        $userId     = $_POST['user_id'];
        $targetId   = $_POST['target_id']; // This is the plot_id
        $targetType = $_POST['target_type']; // Should be 'plot'
        $role       = Validator::sanitize($_POST['assignment_role']);
        $startDate  = $_POST['start_date'];
        $endDate    = $_POST['end_date'];
        $notes      = Validator::sanitize($_POST['notes']);
        // NEW LINE: Retrieve the Planting ID from the form
        $plantingId = $_POST['planting_id'] ?? null; 
        
        // CRITICAL: Call the updated Model function with the new Planting ID
        $assignmentModel->assign($userId, $targetType, $targetId, $role, $startDate, $endDate, $notes, $plantingId);

        Session::set('flash_success', 'Worker assigned successfully to plot and crop.');

        // Redirect back to the Farm Detail page where Plot Management happens
        $farm_id = $_POST['farm_id_redirect']; 
        header("Location: " . BASE_URL . "farm_details.php?id=" . $farm_id . "&tab=plots");
        
    } catch (Exception $e) {
        Session::set('flash_error', "Assignment Error: " . $e->getMessage());
        // Redirect back to the previous page (Plot Management)
        header("Location: " . $_SERVER['HTTP_REFERER']); 
    }
    exit;
}
?>