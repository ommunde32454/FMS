<?php
// views/admin/settings.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireRole(['superadmin']);

require_once __DIR__ . '/../../templates/header.php';
?>

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

    <div class="flex-grow p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Global Settings</h1>
        
        <div class="bg-white p-8 rounded shadow max-w-2xl">
            <form>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Application Name</label>
                    <input type="text" value="<?php echo APP_NAME; ?>" class="w-full border p-2 rounded bg-gray-100 cursor-not-allowed" readonly>
                    <p class="text-xs text-gray-500 mt-1">Defined in config.php</p>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Timezone</label>
                    <select class="w-full border p-2 rounded">
                        <option value="Asia/Kolkata" selected>Asia/Kolkata</option>
                        <option value="UTC">UTC</option>
                        <option value="America/New_York">America/New_York</option>
                    </select>
                </div>

                <div class="flex items-center mb-6">
                    <input type="checkbox" id="maintenance" class="mr-2 h-4 w-4">
                    <label for="maintenance" class="text-gray-700">Enable Maintenance Mode</label>
                </div>

                <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed">Save Changes (Demo Only)</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>