<?php
// views/inventory/stock.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// --- LOGIC: Fetch Farms & Inventory based on Role ---

// 1. Determine which Farms are accessible
if ($role === 'owner') {
    // Get My Owner Profile ID
    $stmtO = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
    $stmtO->execute([$userId]);
    $myOwnerId = $stmtO->fetchColumn();

    if ($myOwnerId) {
        // Fetch ONLY my active farms
        $stmt = $db->prepare("SELECT farm_id, farm_name FROM farms WHERE owner_id = ? AND status='active'");
        $stmt->execute([$myOwnerId]);
        $farms = $stmt->fetchAll();
    } else {
        $farms = []; // No profile linked
    }
} else {
    // Admin/Manager sees ALL active farms
    $farms = $db->query("SELECT farm_id, farm_name FROM farms WHERE status='active'")->fetchAll();
}

// 2. Build Inventory Query
$sql = "SELECT i.*, f.farm_name 
        FROM inventory_items i 
        JOIN farms f ON i.farm_id = f.farm_id 
        WHERE 1=1";

$params = [];

// Apply Filters
if ($role === 'owner') {
    if (empty($farms)) {
        // If owner has no farms, show nothing
        $sql .= " AND 1=0"; 
    } else {
        // Filter by the farms this owner actually owns
        $myFarmIds = array_column($farms, 'farm_id');
        // Create placeholders like (?,?,?)
        $placeholders = implode(',', array_fill(0, count($myFarmIds), '?'));
        $sql .= " AND i.farm_id IN ($placeholders)";
        $params = array_merge($params, $myFarmIds);
    }
} else {
    // Admin/Manager filter via Dropdown
    $filter_farm_id = $_GET['farm_id'] ?? '';
    if ($filter_farm_id) {
        $sql .= " AND i.farm_id = ?";
        $params[] = $filter_farm_id;
    }
}

$sql .= " ORDER BY i.item_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Inventory Stock</h1>
    
    <div class="flex gap-2">
        <!-- HIDE Actions for Owners -->
        <?php if($role !== 'owner'): ?>
            <a href="low_stock.php" class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200">
                <i class="fas fa-exclamation-triangle mr-1"></i> Alerts
            </a>
            <button onclick="toggleModal('addItemModal')" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
                <i class="fas fa-plus mr-1"></i> Add Item
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Bar (Only for Admin/Manager) -->
<?php if($role !== 'owner'): ?>
<div class="mb-4">
    <form method="GET" class="flex items-center">
        <label class="mr-2 text-sm font-bold text-gray-600">Filter by Farm:</label>
        <select name="farm_id" onchange="this.form.submit()" class="border rounded px-3 py-1 text-sm bg-white">
            <option value="">All Farms</option>
            <?php foreach($farms as $f): ?>
                <option value="<?php echo $f['farm_id']; ?>" <?php echo ($filter_farm_id ?? '') === $f['farm_id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['farm_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php endif; ?>

<!-- Stock Table -->
<div class="bg-white shadow rounded overflow-hidden mb-8">
    <table class="min-w-full leading-normal">
        <thead class="bg-gray-100 text-xs font-bold uppercase text-gray-600">
            <tr>
                <th class="px-5 py-3 text-left">Item Name</th>
                <th class="px-5 py-3 text-left">Farm</th>
                <th class="px-5 py-3 text-left">Category</th>
                <th class="px-5 py-3 text-right">Qty</th>
                <!-- Hide Action Column Header for Owners -->
                <?php if($role !== 'owner'): ?>
                    <th class="px-5 py-3 text-center">Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm">
            <?php foreach($items as $item): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-5 py-3 font-medium"><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td class="px-5 py-3 text-gray-500"><?php echo htmlspecialchars($item['farm_name']); ?></td>
                <td class="px-5 py-3">
                    <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs">
                        <?php echo htmlspecialchars($item['category']); ?>
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-bold">
                    <?php echo floatval($item['quantity_available']) . ' ' . $item['unit']; ?>
                </td>
                
                <!-- Hide Update Button for Owners -->
                <?php if($role !== 'owner'): ?>
                <td class="px-5 py-3 text-center">
                    <button onclick="openTxnModal('<?php echo $item['inv_id']; ?>', '<?php echo htmlspecialchars($item['item_name']); ?>')" 
                            class="text-blue-600 hover:text-blue-900 text-xs font-bold uppercase">
                        Update
                    </button>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            
            <?php if(empty($items)): ?>
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-500">
                        <?php echo ($role === 'owner') ? "No inventory items found for your farms." : "No inventory records found."; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modals (Only render if User has permission) -->
<?php if($role !== 'owner'): ?>

    <!-- Modal: Add New Item -->
    <div id="addItemModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg p-6 relative">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Add Inventory Item</h3>
                <button onclick="toggleModal('addItemModal')" class="text-gray-500 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>controllers/inventory_add.php" method="POST">
                <?php echo CSRF::input(); ?>
                
                <div class="mb-3">
                    <label class="block text-xs font-bold uppercase mb-1">Target Farm</label>
                    <select name="farm_id" required class="w-full border rounded p-2 bg-white">
                        <?php foreach($farms as $f): ?>
                            <option value="<?php echo $f['farm_id']; ?>">
                                <?php echo htmlspecialchars($f['farm_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold uppercase mb-1">Item Name</label>
                    <input type="text" name="item_name" placeholder="e.g. Urea 46%" required class="w-full border rounded p-2">
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Category</label>
                        <select name="category" class="w-full border rounded p-2 bg-white">
                            <option value="fertilizer">Fertilizer</option>
                            <option value="seed">Seed</option>
                            <option value="pesticide">Pesticide</option>
                            <option value="fuel">Fuel</option>
                            <option value="tool">Tool/Equip</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Batch #</label>
                        <input type="text" name="batch_number" class="w-full border rounded p-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Quantity</label>
                        <input type="number" step="0.01" name="quantity" required class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Unit</label>
                        <input type="text" name="unit" placeholder="kg, L, bags" required class="w-full border rounded p-2">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" class="w-full border rounded p-2">
                </div>

                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 rounded hover:bg-emerald-700">
                    Save Item
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Record Transaction -->
    <div id="txnModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-sm mx-auto rounded shadow-lg p-6 relative">
            <h3 class="text-lg font-bold mb-1">Update Stock</h3>
            <p id="txnItemName" class="text-sm text-gray-500 mb-4">Item Name</p>
            
            <form action="<?php echo BASE_URL; ?>controllers/inventory_use.php" method="POST">
                <?php echo CSRF::input(); ?>
                <input type="hidden" name="inv_id" id="txnInvId">

                <div class="mb-3">
                    <label class="block text-xs font-bold uppercase mb-1">Action Type</label>
                    <select name="txn_type" class="w-full border rounded p-2 bg-white">
                        <option value="use">Use (Subtract)</option>
                        <option value="purchase">Purchase (Add)</option>
                        <option value="loss">Loss/Waste (Subtract)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold uppercase mb-1">Quantity</label>
                    <input type="number" step="0.01" name="quantity" required class="w-full border rounded p-2">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase mb-1">Notes / Reason</label>
                    <textarea name="notes" rows="2" class="w-full border rounded p-2"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('txnModal').classList.add('hidden')" class="text-gray-500">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openTxnModal(id, name) {
            document.getElementById('txnInvId').value = id;
            document.getElementById('txnItemName').innerText = name;
            toggleModal('txnModal');
        }
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>