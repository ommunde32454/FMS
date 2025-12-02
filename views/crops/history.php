<?php
// views/crops/history.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$params = [];

// 1. DATA ISOLATION SETUP (Same logic as calendar.php)
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

// 2. Fetch Harvested Crops
$sql = "SELECT p.*, c.name as crop_name, pl.plot_name, f.farm_name 
        FROM plantings p
        JOIN crop_types c ON p.crop_type_id = c.crop_type_id
        JOIN plots pl ON p.plot_id = pl.plot_id
        JOIN farms f ON pl.farm_id = f.farm_id
        WHERE p.status = 'harvested'
        $farmFilterSql
        ORDER BY p.actual_harvest_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Harvest History</h1>
        <!-- Navigation Tabs -->
        <div class="flex gap-4 text-sm mt-1">
            <a href="crops.php" class="text-gray-500 hover:text-emerald-600 transition">Growing</a>
            <a href="crops.php?view=history" class="text-emerald-600 font-bold border-b-2 border-emerald-600">Harvest History</a>
            <a href="crops.php?view=calendar" class="text-gray-500 hover:text-emerald-600 transition">Calendar</a>
        </div>
    </div>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-xs font-bold uppercase text-gray-600">
                <th class="px-5 py-3 text-left">Harvest Date</th>
                <th class="px-5 py-3 text-left">Crop</th>
                <th class="px-5 py-3 text-left">Location</th>
                <th class="px-5 py-3 text-right">Yield</th>
                <th class="px-5 py-3 text-left pl-8">Notes</th>
            </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
            <?php foreach($history as $h): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-gray-600">
                    <?php echo date('M d, Y', strtotime($h['actual_harvest_date'])); ?>
                </td>
                <td class="px-5 py-3 font-bold text-gray-800">
                    <?php echo htmlspecialchars($h['crop_name']); ?>
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">
                    <?php echo htmlspecialchars($h['farm_name']); ?> <br>
                    <?php echo htmlspecialchars($h['plot_name']); ?>
                </td>
                <td class="px-5 py-3 text-right font-bold text-emerald-600">
                    <?php echo floatval($h['yield_quantity']) . ' ' . $h['yield_unit']; ?>
                </td>
                <td class="px-5 py-3 pl-8 text-xs text-gray-400 italic">
                    Planted: <?php echo date('M d, Y', strtotime($h['planting_date'])); ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($history)): ?>
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500">No harvest records found <?php echo ($role === 'owner') ? 'for your properties.' : '.'; ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>