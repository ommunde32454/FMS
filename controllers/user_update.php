<?php
// controllers/user_update.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireRole(['superadmin']); // Only Super Admin can modify users

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $userId = $_POST['user_id'];
    $name = Validator::sanitize($_POST['name']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $currentUserId = Session::get('user_id');

    try {
        $db->beginTransaction();

        // 1. Update User Record
        $sql = "UPDATE users SET name = ?, role = ?, status = ? WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $role, $status, $userId]);
        
        // 2. Critical: Sync Owner Profile if role is 'owner'
        if ($role === 'owner') {
            // Check if owner profile exists, if not, create it
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM owners WHERE user_id = ?");
            $stmtCheck->execute([$userId]);
            
            if ($stmtCheck->fetchColumn() == 0) {
                // If profile doesn't exist, create a basic one
                $email = Validator::sanitize($_POST['email']);
                $ownerUuid = generate_uuid();
                $sqlOwner = "INSERT INTO owners (owner_id, user_id, display_name, phone, email) 
                             VALUES (?, ?, ?, '0000000000', ?)";
                $stmtOwner = $db->prepare($sqlOwner);
                $stmtOwner->execute([$ownerUuid, $userId, $name, $email]);
            }
        }
        
        // 3. If the user being updated is the logged-in user, refresh their session role
        if ($userId === $currentUserId) {
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;
        }

        (new Audit($db))->log($currentUserId, 'USER_UPDATE', 'users', $userId, null, $_POST);
        
        $db->commit();
        Session::set('flash_success', "User $name updated successfully.");

    } catch (Exception $e) {
        $db->rollBack();
        Session::set('flash_error', "Error updating user: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>