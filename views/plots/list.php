<?php
// views/plots/list.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

// Filter by Farm if needed
$farm_id = $_GET['farm_id'] ?? '';
$sql = "SELECT p.*, f.farm_name 
        FROM plots p 
        JOIN farms f ON p.farm_id = f.farm_id 
        WHERE 1=1";
$params = [];

if ($farm_id) {
    $sql .= " AND p.farm_id = ?";
    $params[] = $farm_id;
}
$sql .= " ORDER BY f.farm_name, p.plot_name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$plots = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Plot Management</h1>
        <p class="text-gray-500 text-sm">Manage individual fields and soil status.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>farms.php" class="text-emerald-600 hover:underline text-sm">
        Go to Farms to Add Plot &rarr;
    </a>
</div>

<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                <th class="px-5 py-3 text-left">Plot Name</th>
                <th class="px-5 py-3 text-left">Parent Farm</th>
                <th class="px-5 py-3 text-right">Area</th>
                <th class="px-5 py-3 text-left pl-8">Soil Notes</th>
                <th class="px-5 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm">
            <?php foreach($plots as $p): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-5 py-3 font-bold text-emerald-700">
                    <?php echo htmlspecialchars($p['plot_name']); ?>
                </td>
                <td class="px-5 py-3 text-gray-600">
                    <i class="fas fa-tractor text-xs mr-1"></i>
                    <?php echo htmlspecialchars($p['farm_name']); ?>
                </td>
                <td class="px-5 py-3 text-right font-mono">
                    <?php echo number_format($p['area_sqm'], 2); ?> m²
                </td>
                <td class="px-5 py-3 pl-8 text-gray-500 italic truncate max-w-xs">
                    <?php echo htmlspecialchars($p['soil_health_notes'] ?? 'No notes'); ?>
                </td>
                <td class="px-5 py-3 text-center">
                    <a href="manage.php?id=<?php echo $p['plot_id']; ?>" class="bg-gray-100 text-blue-600 hover:bg-blue-100 px-3 py-1 rounded text-xs font-bold">
                        Edit
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>