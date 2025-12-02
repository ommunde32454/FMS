<?php
// views/crops/calendar.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$params = [];

// 1. DATA ISOLATION SETUP
$farmFilterSql = "";
if ($role === 'owner') {
    $stmtO = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
    $stmtO->execute([$userId]);
    $myOwnerId = $stmtO->fetchColumn();

    if ($myOwnerId) {
        $farmFilterSql = " AND f.owner_id = ?";
        $params[] = $myOwnerId;
    } else {
        $farmFilterSql = " AND 1=0";
    }
}

// 2. Fetch upcoming events (Planting date OR Harvest date)
$sql = "SELECT p.*, c.name as crop_name, f.farm_name 
        FROM plantings p 
        JOIN crop_types c ON p.crop_type_id = c.crop_type_id
        JOIN plots pl ON p.plot_id = pl.plot_id
        JOIN farms f ON pl.farm_id = f.farm_id
        WHERE p.status = 'growing'
        $farmFilterSql
        ORDER BY p.expected_harvest_date ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Harvest Calendar</h1>
        <!-- Navigation Tabs -->
        <div class="flex gap-4 text-sm mt-1">
            <a href="crops.php" class="text-gray-500 hover:text-emerald-600 transition">Growing</a>
            <a href="crops.php?view=history" class="text-gray-500 hover:text-emerald-600 transition">Harvest History</a>
            <a href="crops.php?view=calendar" class="text-emerald-600 font-bold border-b-2 border-emerald-600">Calendar</a>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded shadow">
    <h3 class="font-bold text-gray-700 mb-4">Upcoming Harvests</h3>
    
    <div class="space-y-4">
        <?php foreach($events as $e): ?>
            <?php 
                $date = strtotime($e['expected_harvest_date']);
                $month = date('M', $date);
                $day = date('d', $date);
            ?>
            <div class="flex items-center p-3 border rounded hover:bg-emerald-50 transition">
                <!-- Date Box -->
                <div class="flex-shrink-0 w-16 h-16 bg-emerald-100 text-emerald-600 rounded flex flex-col items-center justify-center mr-4">
                    <span class="text-xs font-bold uppercase"><?php echo $month; ?></span>
                    <span class="text-2xl font-bold"><?php echo $day; ?></span>
                </div>
                
                <!-- Details -->
                <div class="flex-grow">
                    <h4 class="font-bold text-lg"><?php echo htmlspecialchars($e['crop_name']); ?></h4>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($e['farm_name']); ?>
                    </p>
                </div>

                <!-- Status -->
                <div class="text-right">
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Expected</span>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($events)): ?>
            <p class="text-gray-500 text-center py-4">No upcoming harvests scheduled <?php echo ($role === 'owner') ? 'for your properties.' : '.'; ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>