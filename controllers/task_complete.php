<?php
// controllers/task_complete.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();
$currentUserId = Session::get('user_id');
$currentUserRole = Session::get('role');

if ($currentUserRole !== 'field_worker') {
    Session::set('flash_error', 'Access Denied. Only Field Workers can complete tasks.');
    header("Location: " . BASE_URL . "crops.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $plotId = $_POST['plot_id']; // Target is the plot
    
    try {
        $db->beginTransaction();
        $assignmentModel = new Assignment($db);

        // 1. Mark the worker's specific assignment for this plot as INACTIVE
        $sql = "UPDATE assignments 
                SET active = FALSE, end_date = NOW(), notes = CONCAT(notes, ' | Task marked complete by worker.')
                WHERE user_id = ? AND target_type = 'plot' AND target_id = ? AND active = TRUE";
        $stmt = $db->prepare($sql);
        $stmt->execute([$currentUserId, $plotId]);

        // 2. Audit Log
        (new Audit($db))->log($currentUserId, 'TASK_COMPLETE', 'assignments', $plotId, null, ['status' => 'completed']);
        
        $db->commit();
        Session::set('flash_success', 'Task successfully marked as complete.');
    } catch (Exception $e) {
        $db->rollBack();
        Session::set('flash_error', "Error completing task: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "crops.php");
    exit;
}
?>