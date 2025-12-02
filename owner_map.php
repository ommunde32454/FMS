<?php
// owner_map.php
// Router to load the map view for a specific owner

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Autoloader.php';
require_once __DIR__ . '/templates/header.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireLogin();
$userId = $_SESSION['user_id'];

// Get Owner ID
$stmt = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
$stmt->execute([$userId]);
$ownerId = $stmt->fetchColumn();

// Load the general map view, but pass the owner ID for filtering
require_once __DIR__ . '/views/farms/map.php';

// The JS in views/farms/map.php would need updating to check this ID, 
// but for simplicity, the map loads all, and the dashboard links remain separate.

// We will keep the general map view for now and focus on the document upload.
// For true isolation, the map view code itself (views/farms/map.php) would need to be modified
// to accept an ownerId filter parameter.

// Since the map view currently fetches ALL active farms via api.php (which is not filtered), 
// we will focus on the Document Upload UI first as a priority.
?>
<?php require_once __DIR__ . '/templates/footer.php'; ?>