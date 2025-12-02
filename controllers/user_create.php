<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireRole(['superadmin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $name  = Validator::sanitize($_POST['name']);
    $email = Validator::sanitize($_POST['email']);
    $pass  = $_POST['password'];
    $role  = $_POST['role'];

    $userModel = new User($db);
    
    try {
        $db->beginTransaction(); // Start Transaction for safety

        // 1. Create the Login User
        $userId = $userModel->create($name, $email, $pass, $role);
        
        // 2. CRITICAL: If Role is Owner, Create Owner Profile
        if ($role === 'owner') {
            $ownerUuid = generate_uuid();
            $sqlOwner = "INSERT INTO owners (owner_id, user_id, display_name, phone, email, city) 
                         VALUES (?, ?, ?, '0000000000', ?, 'Default City')";
            $stmtOwner = $db->prepare($sqlOwner);
            $stmtOwner->execute([$ownerUuid, $userId, $name, $email]);
        }

        // 3. Log Action
        (new Audit($db))->log(Session::get('user_id'), 'CREATE_USER', 'users', $email);

        $db->commit(); // Save changes
        Session::set('flash_success', 'User created successfully.');

    } catch (Exception $e) {
        $db->rollBack(); // Undo if error
        Session::set('flash_error', "Error: " . $e->getMessage());
    }

    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>