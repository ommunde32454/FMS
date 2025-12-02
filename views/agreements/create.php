<?php
// views/agreements/create.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();

// Fetch Farms & Owners for Dropdowns
$farms = $db->query("SELECT farm_id, farm_name FROM farms WHERE status='active'")->fetchAll();
$owners = $db->query("SELECT owner_id, display_name FROM owners ORDER BY display_name")->fetchAll();
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded shadow mt-6">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Upload New Agreement</h2>
        <!-- FIXED LINK: Points back to main agreements list -->
        <a href="<?php echo BASE_URL; ?>agreements.php" class="text-gray-500 hover:text-gray-700">Cancel</a>
    </div>

    <form action="<?php echo BASE_URL; ?>controllers/agreement_save.php" method="POST" enctype="multipart/form-data">
        <?php echo CSRF::input(); ?>

        <!-- Farm & Owner Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Select Farm</label>
                <select name="farm_id" required class="w-full border rounded p-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Choose Farm --</option>
                    <?php foreach ($farms as $f): ?>
                        <option value="<?php echo $f['farm_id']; ?>"><?php echo htmlspecialchars($f['farm_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Farm Owner</label>
                <select name="owner_id" required class="w-full border rounded p-2 bg-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Choose Owner --</option>
                    <?php foreach ($owners as $o): ?>
                        <option value="<?php echo $o['owner_id']; ?>"><?php echo htmlspecialchars($o['display_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Duration Dates -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                <input type="date" name="start_date" required class="w-full border rounded p-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">End Date (Expiry)</label>
                <input type="date" name="end_date" required class="w-full border rounded p-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Terms Summary -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Key Terms Summary</label>
            <textarea name="terms" rows="3" class="w-full border rounded p-2 focus:ring-blue-500" placeholder="e.g. 50% profit share, water costs handled by manager..."></textarea>
            <p class="text-xs text-gray-500 mt-1">Brief notes for quick reference.</p>
        </div>

        <!-- File Upload -->
        <div class="mb-8">
            <label class="block text-gray-700 text-sm font-bold mb-2">Signed Contract (PDF/Image)</label>
            <div class="flex items-center justify-center w-full">
                <label class="flex flex-col w-full h-32 border-2 border-blue-200 border-dashed hover:bg-gray-100 hover:border-gray-300">
                    <div class="flex flex-col items-center justify-center pt-7">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="pt-1 text-sm tracking-wider text-gray-400 group-hover:text-gray-600">
                            Select a file
                        </p>
                    </div>
                    <input type="file" name="contract_file" class="opacity-0" required accept=".pdf,.jpg,.jpeg,.png" />
                </label>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-6 rounded hover:bg-blue-700 transition shadow-lg transform hover:-translate-y-1">
                Save Agreement
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>