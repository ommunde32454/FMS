<?php
$db = Database::getInstance()->getConnection();
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$assignmentModel = new Assignment($db); // Requires src/Models/Assignment.php to be present!

$activeCrops = 0;
$assignedPlotsCount = 0;

if ($role === 'field_worker') {
    // Get count of tasks assigned to this worker
    $assignedPlotIds = $assignmentModel->getActivePlotIdsByUser($userId);
    $activeCrops = count($assignedPlotIds);
} else {
    // Manager/Admin Logic: See all growing crops
    $activeCrops = $db->query("SELECT count(*) FROM plantings WHERE status = 'growing'")->fetchColumn();
}
?>
<h2 class="text-2xl font-bold mb-4">
    <?php echo ($role === 'field_worker') ? 'Field Worker Task Board' : 'Manager Dashboard'; ?>
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded shadow border-l-4 border-emerald-500">
        <h3 class="font-bold text-gray-700 mb-2">
            <?php echo ($role === 'field_worker') ? 'Active Assigned Tasks' : 'Active Growing Crops'; ?>
        </h3>
        <p class="text-4xl font-bold text-emerald-600"><?php echo $activeCrops; ?></p>
        <a href="crops.php" class="text-sm text-blue-500 hover:underline mt-2 inline-block">Go to Task List</a>
    </div>
    
    <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
        <h3 class="font-bold text-gray-700 mb-2">Quick Actions</h3>
        <div class="flex gap-2 flex-wrap">
            <!-- Field Worker Actions -->
            <?php if ($role === 'field_worker'): ?>
                <a href="crops.php" class="bg-blue-100 text-blue-700 px-3 py-2 rounded hover:bg-blue-200">
                    <i class="fas fa-check-square mr-1"></i> Check My Tasks
                </a>
                <!-- Restrict access to inventory manipulation -->
                <span class="bg-gray-100 text-gray-500 px-3 py-2 rounded text-sm cursor-not-allowed">
                    <i class="fas fa-box mr-1"></i> Inventory (Restricted)
                </span>
            <?php else: ?>
                <!-- Manager/Admin Actions -->
                <a href="crops.php?action=create" class="bg-emerald-100 text-emerald-700 px-3 py-2 rounded hover:bg-emerald-200">
                    <i class="fas fa-plus mr-1"></i> New Planting
                </a>
                <a href="inventory.php" class="bg-blue-100 text-blue-700 px-3 py-2 rounded hover:bg-blue-200">
                    <i class="fas fa-box mr-1"></i> Use Inventory
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>