<?php
// views/crops/types.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

// Handle Add Crop Type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cropModel = new Crop($db);
    $cropModel->addType($_POST['name'], $_POST['days'], $_POST['desc']);
    // FIXED: Redirect to the correct router URL
    echo "<script>window.location.href='" . BASE_URL . "crops.php?view=types';</script>";
    exit;
}

// Fetch Types
$types = (new Crop($db))->getAllTypes();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Crop Varieties (Catalog)</h1>
    <button onclick="toggleModal('addTypeModal')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
        <i class="fas fa-plus mr-1"></i> Add Variety
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach($types as $t): ?>
    <div class="bg-white p-5 rounded shadow border-t-4 border-indigo-500">
        <h3 class="font-bold text-lg"><?php echo htmlspecialchars($t['name']); ?></h3>
        <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($t['description']); ?></p>
        <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-2 rounded">
            <i class="fas fa-clock mr-2 text-indigo-400"></i>
            Cycle: <strong class="mx-1"><?php echo $t['typical_cycle_days']; ?></strong> days
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Modal -->
<div id="addTypeModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-96 rounded shadow p-6">
        <h3 class="font-bold mb-4">Add Crop Variety</h3>
        <!-- Form submits to current URL -->
        <form method="POST">
            <input type="text" name="name" placeholder="Name (e.g. Wheat HD29)" required class="w-full border p-2 mb-3 rounded">
            <input type="number" name="days" placeholder="Cycle Days (e.g. 120)" required class="w-full border p-2 mb-3 rounded">
            <textarea name="desc" placeholder="Description" class="w-full border p-2 mb-4 rounded"></textarea>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Save</button>
            <button type="button" onclick="toggleModal('addTypeModal')" class="w-full mt-2 text-gray-500">Cancel</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>