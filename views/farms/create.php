<?php
// views/farms/create.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
// Fetch owners for dropdown
$owners = $db->query("SELECT owner_id, display_name FROM owners ORDER BY display_name ASC")->fetchAll();
?>

<div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add New Farm</h2>
        <a href="index.php" class="text-gray-500 hover:text-gray-700">Cancel</a>
    </div>

    <form action="<?php echo BASE_URL; ?>controllers/farm_save.php" method="POST">
        <?php echo CSRF::input(); ?>
        
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Farm Name <span class="text-red-500">*</span></label>
            <input type="text" name="farm_name" required class="w-full border rounded p-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Owner <span class="text-red-500">*</span></label>
            <select name="owner_id" required class="w-full border rounded p-2 bg-white">
                <option value="">-- Select Owner --</option>
                <?php foreach ($owners as $owner): ?>
                    <option value="<?php echo $owner['owner_id']; ?>"><?php echo htmlspecialchars($owner['display_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Survey / Reg No.</label>
                <input type="text" name="survey_number" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Total Area (sq meters)</label>
                <input type="number" step="0.01" name="area" class="w-full border rounded p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Latitude</label>
                <input type="text" name="lat" placeholder="e.g. 18.5204" class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Longitude</label>
                <input type="text" name="lng" placeholder="e.g. 73.8567" class="w-full border rounded p-2">
            </div>
        </div>

        <input type="hidden" name="boundary_polygon" id="boundary_polygon">

        <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded hover:bg-emerald-700 transition">
            Save Farm
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>