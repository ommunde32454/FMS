<?php
// views/crops/active.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$params = [];
$assignmentModel = new Assignment($db);
$farmFilterSql = "";

// 1. DATA ISOLATION SETUP FOR ALL ROLES
if ($role === 'field_worker') {
    // Filter by assigned plots only
    $assignedPlotIds = $assignmentModel->getActivePlotIdsByUser($userId);
    
    if (empty($assignedPlotIds)) {
        $farmFilterSql = " AND 1=0";
    } else {
        // Build SQL placeholders for the assigned plots
        $placeholders = implode(',', array_fill(0, count($assignedPlotIds), '?'));
        $farmFilterSql = " AND p.plot_id IN ($placeholders)";
        $params = $assignedPlotIds;
    }
} elseif ($role === 'owner') {
    // Filter by the owner's farms only
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
// Admin/Manager views all (no filter applied)

// 2. Fetch Active Plantings
$sql = "SELECT p.*, c.name as crop_name, pl.plot_name, pl.plot_id, f.farm_name, f.farm_id 
        FROM plantings p
        JOIN crop_types c ON p.crop_type_id = c.crop_type_id
        JOIN plots pl ON p.plot_id = pl.plot_id
        JOIN farms f ON pl.farm_id = f.farm_id
        WHERE p.status IN ('planted', 'growing')
        $farmFilterSql
        ORDER BY p.expected_harvest_date ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$plantings = $stmt->fetchAll();

// 3. Fetch Data for "Add Planting" Dropdowns (Only visible to Manager/Admin/Owner)
$activePlots = [];
$cropTypes = [];
if ($role !== 'field_worker') {
    $plotsSql = "SELECT p.plot_id, p.plot_name, f.farm_name 
                 FROM plots p 
                 JOIN farms f ON p.farm_id = f.farm_id 
                 WHERE p.status = 'active'";
    
    // --- UPDATED LOGIC: Filter plots for planting based on owner ---
    $plotParams = [];
    $plotFilter = "";
    if ($role === 'owner') {
        // If owner, limit planting to plots on their own farms
        $ownerCheck = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
        $ownerCheck->execute([$userId]);
        $ownerPlotId = $ownerCheck->fetchColumn();
        
        if ($ownerPlotId) {
            $plotsSql .= " AND f.owner_id = ?";
            $plotParams[] = $ownerPlotId;
        } else {
            $plotsSql .= " AND 1=0";
        }
    }
    
    $stmtPlots = $db->prepare($plotsSql);
    $stmtPlots->execute($plotParams); 
    $activePlots = $stmtPlots->fetchAll();

    $typesSql = "SELECT crop_type_id, name, typical_cycle_days FROM crop_types ORDER BY name ASC";
    $cropTypes = $db->query($typesSql)->fetchAll();
}
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            <?php echo ($role === 'field_worker') ? 'My Assigned Tasks (Growing Crops)' : 'Active Crops'; ?>
        </h1>
        <!-- NAVIGATION TABS -->
        <div class="flex gap-4 text-sm mt-1">
            <a href="crops.php" class="text-emerald-600 font-bold border-b-2 border-emerald-600">Growing</a>
            
            <!-- Hide sensitive data views from Field Workers -->
            <?php if ($role !== 'field_worker'): ?>
                <a href="crops.php?view=history" class="text-gray-500 hover:text-gray-800 transition">Harvest History</a>
                <a href="crops.php?view=calendar" class="text-gray-500 hover:text-gray-800 transition">Calendar</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- HIDE 'New Planting' from Field Workers -->
    <?php if ($role !== 'field_worker'): ?>
        <button onclick="toggleModal('addPlantingModal')" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700 shadow flex items-center transition">
            <i class="fas fa-seedling mr-2"></i> New Planting
        </button>
    <?php endif; ?>
</div>

<!-- Active Crops Table -->
<div class="bg-white shadow rounded-lg overflow-hidden mb-8">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-xs font-bold uppercase text-gray-600">
                <th class="px-5 py-3 text-left">Crop / Task</th>
                <th class="px-5 py-3 text-left">Location (Farm/Plot)</th>
                <th class="px-5 py-3 text-left">Planted On</th>
                <th class="px-5 py-3 text-left">Expected Finish</th>
                <th class="px-5 py-3 text-center">Days Left</th>
                <th class="px-5 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
            <?php foreach($plantings as $p): ?>
                <?php
                    $daysLeft = ceil((strtotime($p['expected_harvest_date']) - time()) / 86400);
                    $daysColor = $daysLeft < 10 ? 'text-red-600 font-bold' : 'text-emerald-600';
                ?>
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-5 py-3 font-bold text-emerald-800"><?php echo htmlspecialchars($p['crop_name']); ?></td>
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($p['farm_name']); ?></div>
                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($p['plot_name']); ?></div>
                </td>
                <td class="px-5 py-3 text-gray-500"><?php echo date('M d, Y', strtotime($p['planting_date'])); ?></td>
                <td class="px-5 py-3 font-medium"><?php echo date('M d, Y', strtotime($p['expected_harvest_date'])); ?></td>
                <td class="px-5 py-3 text-center <?php echo $daysColor; ?>"><?php echo $daysLeft; ?> days</td>
                <td class="px-5 py-3 text-center">
                    <!-- ACTION LOGIC FOR WORKER VS MANAGER -->
                    <?php if ($role === 'field_worker'): ?>
                        <!-- Field Worker Action: Mark Task Complete -->
                        <button onclick="toggleModal('completeTaskModal<?php echo $p['plot_id']; ?>')" 
                                class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-1 rounded text-xs font-bold transition">
                            Complete Task
                        </button>
                    <?php elseif (in_array($role, ['superadmin', 'manager'])): ?>
                        <!-- Manager/Admin Action: Harvest (Finalizing the crop yield) -->
                        <button onclick="openHarvestModal('<?php echo $p['planting_id']; ?>', '<?php echo htmlspecialchars($p['crop_name']); ?>')" 
                                class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded text-xs font-bold transition">
                            Harvest
                        </button>
                    <?php else: ?>
                         <span class="text-xs text-gray-400">View Only</span>
                    <?php endif; ?>
                </td>
            </tr>
            
            <!-- MODAL: Complete Task (Worker Only) -->
            <?php if ($role === 'field_worker'): ?>
            <div id="completeTaskModal<?php echo $p['plot_id']; ?>" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white w-96 rounded shadow p-6 relative">
                    <h3 class="font-bold mb-2 text-lg">Mark Task Complete</h3>
                    <p class="text-sm text-gray-500 mb-4">Confirm completion for plot: <span class="font-bold"><?php echo htmlspecialchars($p['plot_name']); ?></span></p>
                    
                    <!-- Form submits to task_complete.php -->
                    <form action="<?php echo BASE_URL; ?>controllers/task_complete.php" method="POST">
                        <?php echo CSRF::input(); ?>
                        <input type="hidden" name="plot_id" value="<?php echo $p['plot_id']; ?>">
                        
                        <p class="text-red-600 mb-4 text-sm">NOTE: This marks your assignment for this plot as finished.</p>
                        
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleModal('completeTaskModal<?php echo $p['plot_id']; ?>')" class="text-gray-500 px-3 py-2">Cancel</button>
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded font-bold hover:bg-yellow-700">Confirm Complete</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <?php endforeach; ?>
            
            <?php if(empty($plantings)): ?>
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500 bg-gray-50 border-t border-gray-100">
                        <i class="fas fa-seedling text-4xl mb-3 text-gray-300 block"></i>
                        <br>
                        <?php 
                            if ($role === 'field_worker') {
                                echo "You currently have no active assignments.";
                            } else {
                                echo "No active crops found. Click 'New Planting' to start.";
                            }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL: New Planting (Only render if user can plant) -->
<?php if ($role !== 'field_worker'): ?>
    <div id="addPlantingModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg p-6 relative">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-lg font-bold text-emerald-800">Record New Planting</h3>
                <button onclick="toggleModal('addPlantingModal')" class="text-gray-500 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>controllers/crop_plant.php" method="POST">
                <?php echo CSRF::input(); ?>
                
                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase mb-1 text-gray-600">Select Plot / Field</label>
                    <select name="plot_id" required class="w-full border rounded p-2 bg-white focus:ring-emerald-500">
                        <option value="">-- Choose Location --</option>
                        <?php foreach($activePlots as $plot): ?>
                            <option value="<?php echo $plot['plot_id']; ?>">
                                <?php echo htmlspecialchars($plot['farm_name'] . ' - ' . $plot['plot_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(empty($activePlots)): ?>
                        <p class="text-xs text-red-500 mt-1">No active plots found<?php echo ($role === 'owner') ? ' on your properties.' : '.'; ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase mb-1 text-gray-600">Crop Variety</label>
                    <select name="crop_type_id" required class="w-full border rounded p-2 bg-white focus:ring-emerald-500">
                        <option value="">-- Choose Crop --</option>
                        <?php foreach($cropTypes as $type): ?>
                            <option value="<?php echo $type['crop_type_id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?> (~<?php echo $type['typical_cycle_days']; ?> days)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="text-right mt-1">
                        <a href="crops.php?view=types" class="text-xs text-blue-500 hover:underline">+ Add New Variety</a>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase mb-1 text-gray-600">Date Planted</label>
                    <input type="date" name="planting_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full border rounded p-2 focus:ring-emerald-500">
                </div>

                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 rounded hover:bg-emerald-700 transition">
                    Start Planting
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- MODAL: Harvest (Only render if user can harvest) -->
<?php if (in_array($role, ['superadmin', 'manager'])): ?>
    <div id="harvestModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-96 rounded shadow p-6 relative">
            <h3 class="font-bold mb-2 text-lg">Record Harvest</h3>
            <p class="text-sm text-gray-500 mb-4">Crop: <span id="hCropName" class="font-bold text-gray-800"></span></p>
            
            <form action="<?php echo BASE_URL; ?>controllers/crop_harvest.php" method="POST">
                <?php echo CSRF::input(); ?>
                <input type="hidden" name="planting_id" id="hPlantingId">
                
                <label class="block text-xs font-bold uppercase mb-1">Yield Quantity</label>
                <div class="flex gap-2 mb-3">
                    <input type="number" step="0.01" name="yield_quantity" required class="w-2/3 border rounded p-2" placeholder="0.00">
                    <input type="text" name="yield_unit" placeholder="kg/ton" required class="w-1/3 border rounded p-2">
                </div>

                <label class="block text-xs font-bold uppercase mb-1">Actual Date</label>
                <input type="date" name="harvest_date" value="<?php echo date('Y-m-d'); ?>" class="w-full border rounded p-2 mb-4">

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="toggleModal('harvestModal')" class="text-gray-500 px-3 py-2">Cancel</button>
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded font-bold hover:bg-emerald-700">Complete Harvest</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openHarvestModal(id, name) {
            document.getElementById('hPlantingId').value = id;
            document.getElementById('hCropName').innerText = name;
            toggleModal('harvestModal');
        }
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>