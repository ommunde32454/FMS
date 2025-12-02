<?php
// controllers/user_delete.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);

// Security Check 1: Must be Super Admin
$auth->requireRole(['superadmin']);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    
    $userIdToDelete = $_GET['id'];
    $currentUserId = $_SESSION['user_id'];

    // Security Check 2: Prevent deleting yourself
    if ($userIdToDelete === $currentUserId) {
        Session::set('flash_error', 'Security Warning: You cannot delete your own active account.');
        header("Location: " . BASE_URL . "users.php");
        exit;
    }
    
    try {
        $db->beginTransaction();

        // Optional: Get user info for logging before deletion
        $stmt = $db->prepare("SELECT name, email, role FROM users WHERE user_id = ?");
        $stmt->execute([$userIdToDelete]);
        $userDeletedInfo = $stmt->fetch();
        
        if (!$userDeletedInfo) {
            $db->rollBack();
            Session::set('flash_error', 'Error: User not found.');
            header("Location: " . BASE_URL . "users.php");
            exit;
        }

        // 1. Clean up Owner Profile (if applicable)
        $stmtOwner = $db->prepare("DELETE FROM owners WHERE user_id = ?");
        $stmtOwner->execute([$userIdToDelete]);

        // 2. Clean up Assignments (or deactivate)
        $stmtAssign = $db->prepare("UPDATE assignments SET active = FALSE, end_date = NOW() WHERE user_id = ? AND active = TRUE");
        $stmtAssign->execute([$userIdToDelete]);

        // 3. Delete the User record
        $stmtUser = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmtUser->execute([$userIdToDelete]);

        // 4. Log Action
        (new Audit($db))->log($currentUserId, 'USER_DELETE', 'users', $userIdToDelete, $userDeletedInfo, null);
        
        $db->commit();
        Session::set('flash_success', "User '{$userDeletedInfo['name']}' ({$userDeletedInfo['role']}) has been permanently deleted.");

    } catch (Exception $e) {
        $db->rollBack();
        Session::set('flash_error', "Database Error: Could not delete user. " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "users.php");
    exit;
} else {
    // If accessed without an ID, redirect
    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>