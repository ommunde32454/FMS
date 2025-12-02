<?php
// views/plots/manage.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();
(new Auth($db))->requireRole(['superadmin', 'manager']); 

$id = $_GET['id'] ?? null;
if (!$id) die("Plot ID required.");

// Fetch Existing Plot Data
$stmt = $db->prepare("SELECT * FROM plots WHERE plot_id = ?");
$stmt->execute([$id]);
$plot = $stmt->fetch();
if (!$plot) die("Plot not found.");

// Fetch Workers for Assignment Dropdown
$userModel = new User($db);
$workers = $userModel->getAssignmentPersonnel(); 

// Fetch Active Assignments for this Plot
$assignmentModel = new Assignment($db);
$activeAssignments = $assignmentModel->getActiveByTarget($id);

// NEW: Fetch Assignment History for this Plot
$historyStmt = $db->prepare("SELECT a.*, u.name as user_name FROM assignments a JOIN users u ON a.user_id = u.user_id WHERE a.target_id = ? AND a.active = FALSE ORDER BY a.end_date DESC LIMIT 5");
$historyStmt->execute([$id]);
$assignmentHistory = $historyStmt->fetchAll();

// Fetch active crops currently growing on this plot for the assignment dropdown
$plantingsStmt = $db->prepare("SELECT p.planting_id, c.name FROM plantings p JOIN crop_types c ON p.crop_type_id = c.crop_type_id WHERE p.plot_id = ? AND p.status IN ('planted', 'growing')");
$plantingsStmt->execute([$id]);
$activePlantings = $plantingsStmt->fetchAll();

// Handle Plot Update POST (Inline Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['user_id'])) {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $name  = Validator::sanitize($_POST['plot_name']);
    $area  = floatval($_POST['area']);
    $notes = Validator::sanitize($_POST['notes']);
    $status= $_POST['status'];

    $sql = "UPDATE plots SET plot_name=?, area_sqm=?, soil_health_notes=?, status=? WHERE plot_id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$name, $area, $notes, $status, $id]);

    // Redirect back to Farm View
    $farm_id = $plot['farm_id'];
    echo "<script>window.location.href='" . BASE_URL . "farm_details.php?id=$farm_id&tab=plots';</script>";
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Plot: <?php echo htmlspecialchars($plot['plot_name']); ?></h2>
        <a href="<?php echo BASE_URL; ?>farm_details.php?id=<?php echo $plot['farm_id']; ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
            &larr; Back to Farm
        </a>
    </div>

    <!-- MAIN GRID: Plot Details & Assignments -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Column 1: Plot Details Form (Update Plot Info) -->
        <div class="md:col-span-2 bg-white p-6 rounded shadow">
            <h3 class="text-xl font-bold mb-4 border-b pb-2">Plot Configuration</h3>
            <form method="POST">
                <?php echo CSRF::input(); ?>
                <input type="hidden" name="farm_id" value="<?php echo $plot['farm_id']; ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Plot Name</label>
                    <input type="text" name="plot_name" value="<?php echo htmlspecialchars($plot['plot_name']); ?>" required class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Area (sq meters)</label>
                    <input type="number" step="0.01" name="area" value="<?php echo $plot['area_sqm']; ?>" required class="w-full border rounded p-2">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Soil / Health Notes</label>
                    <textarea name="notes" rows="3" class="w-full border rounded p-2"><?php echo htmlspecialchars($plot['soil_health_notes']); ?></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="status" class="w-full border rounded p-2 bg-white">
                        <option value="active" <?php echo $plot['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="fallow" <?php echo $plot['status'] == 'fallow' ? 'selected' : ''; ?>>Fallow (Resting)</option>
                        <option value="maintenance" <?php echo $plot['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
                    Update Plot Details
                </button>
            </form>
        </div>

        <!-- Column 2: Assignments (Assign Worker UI) -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded shadow mb-6">
                <h3 class="text-xl font-bold mb-4 border-b pb-2">Active Assignment</h3>
                
                <?php if(empty($activeAssignments)): ?>
                    <div class="text-center py-6 border border-dashed rounded text-gray-500">
                        No worker currently assigned.
                    </div>
                <?php else: ?>
                    <?php foreach($activeAssignments as $assignment): ?>
                        <div class="p-3 mb-2 bg-emerald-50 rounded border border-emerald-300">
                            <p class="font-bold text-emerald-800"><?php echo htmlspecialchars($assignment['user_name']); ?></p>
                            <p class="text-xs text-gray-700 mt-1">Role: <span class="font-medium"><?php echo htmlspecialchars($assignment['role']); ?></span></p>
                            <p class="text-xs text-gray-500">Until: <?php echo date('M d, Y', strtotime($assignment['end_date'])); ?></p>
                            <p class="text-xs italic text-gray-400 mt-2"><?php echo htmlspecialchars($assignment['notes']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <button onclick="toggleModal('assignWorkerModal')" class="bg-emerald-600 text-white font-bold py-2 px-4 rounded shadow hover:bg-emerald-700 w-full mt-4">
                    <i class="fas fa-user-plus mr-1"></i> Assign New Worker
                </button>
            </div>
            
            <!-- NEW SECTION: Assignment History -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-xl font-bold mb-4 border-b pb-2 text-gray-700">Assignment History</h3>
                
                <?php if(empty($assignmentHistory)): ?>
                    <p class="text-gray-400 text-sm">No completed assignments found.</p>
                <?php else: ?>
                    <?php foreach($assignmentHistory as $history): ?>
                        <div class="p-2 border-b last:border-b-0 text-sm">
                            <p class="font-bold text-gray-700"><?php echo htmlspecialchars($history['user_name']); ?></p>
                            <p class="text-xs text-gray-500">
                                Finished: <span class="text-emerald-600 font-medium"><?php echo date('M d, Y', strtotime($history['end_date'])); ?></span>
                            </p>
                            <p class="text-xs text-gray-400 italic mt-1 truncate" title="<?php echo htmlspecialchars($history['notes']); ?>">
                                Task: <?php echo htmlspecialchars($history['role']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Assign Worker -->
<div id="assignWorkerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg p-6">
        <h3 class="text-lg font-bold mb-4">Assign Worker to <?php echo htmlspecialchars($plot['plot_name']); ?></h3>
        
        <form action="<?php echo BASE_URL; ?>controllers/assignment_save.php" method="POST">
            <?php echo CSRF::input(); ?>
            <input type="hidden" name="target_id" value="<?php echo $id; ?>">
            <input type="hidden" name="target_type" value="plot">
            <input type="hidden" name="farm_id_redirect" value="<?php echo $plot['farm_id']; ?>">
            
            <div class="mb-3">
                <label class="block text-xs font-bold uppercase mb-1">Select Personnel</label>
                <select name="user_id" required class="w-full border rounded p-2 bg-white">
                    <option value="">-- Choose Manager/Worker --</option>
                    <?php foreach($workers as $worker): ?>
                        <option value="<?php echo $worker['user_id']; ?>">
                            <?php echo htmlspecialchars($worker['name']); ?> (<?php echo htmlspecialchars($worker['role'] ?? 'N/A'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- FIELD: Link to Planting (Current Crop) -->
            <div class="mb-3">
                <label class="block text-xs font-bold uppercase mb-1 text-gray-600">Assign to Specific Crop/Task</label>
                <select name="planting_id" class="w-full border rounded p-2 bg-white">
                    <option value="">(Optional) General Plot Assignment</option>
                    <?php if (empty($activePlantings)): ?>
                        <option disabled>-- No active crops on this plot --</option>
                    <?php else: ?>
                        <?php foreach($activePlantings as $p): ?>
                            <option value="<?php echo $p['planting_id']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?> (Current Crop)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-bold uppercase mb-1">Assignment Role/Task</label>
                <input type="text" name="assignment_role" placeholder="e.g. Irrigation Lead, Pest Control" required class="w-full border rounded p-2">
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-xs font-bold uppercase mb-1">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase mb-1">End Date (Expected)</label>
                    <input type="date" name="end_date" required class="w-full border rounded p-2">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold uppercase mb-1">Notes</label>
                <textarea name="notes" rows="2" placeholder="Specific instructions for the job." class="w-full border rounded p-2"></textarea>
            </div>
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleModal('assignWorkerModal')" class="text-gray-500">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Assign</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>