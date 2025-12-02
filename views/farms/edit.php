<?php
// views/farms/edit.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$id = $_GET['id'] ?? null;
$db = Database::getInstance()->getConnection();
$farmModel = new Farm($db);
$farm = $farmModel->getById($id);

if (!$farm) die("Farm not found.");
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Edit Farm: <?php echo htmlspecialchars($farm['farm_name']); ?></h2>
    
    <!-- We point to the same save controller for simplicity, logic would usually be separated -->
    <form action="<?php echo BASE_URL; ?>controllers/farm_save.php" method="POST">
        <?php echo CSRF::input(); ?>
        <!-- Hidden ID tells the controller (if updated) which farm to edit -->
        <input type="hidden" name="farm_id" value="<?php echo $farm['farm_id']; ?>">
        <!-- Re-send owner ID to avoid errors if controller expects it -->
        <input type="hidden" name="owner_id" value="<?php echo $farm['owner_id']; ?>">
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Farm Name</label>
            <input type="text" name="farm_name" value="<?php echo htmlspecialchars($farm['farm_name']); ?>" class="w-full border rounded p-2">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Survey Number</label>
            <input type="text" name="survey_number" value="<?php echo htmlspecialchars($farm['survey_number']); ?>" class="w-full border rounded p-2">
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Latitude</label>
                <input type="text" name="lat" value="<?php echo htmlspecialchars($farm['latitude']); ?>" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Longitude</label>
                <input type="text" name="lng" value="<?php echo htmlspecialchars($farm['longitude']); ?>" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Total Area (sq m)</label>
            <input type="number" step="0.01" name="area" value="<?php echo floatval($farm['area_total_sqm']); ?>" class="w-full border rounded p-2">
        </div>

        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Save Changes</button>
        <a href="../../farms.php" class="text-gray-500 ml-4 hover:underline">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>