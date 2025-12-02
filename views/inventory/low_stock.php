<?php
// views/inventory/low_stock.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

// Fetch items where qty < 10 (or custom threshold)
$sql = "SELECT i.*, f.farm_name 
        FROM inventory_items i 
        JOIN farms f ON i.farm_id = f.farm_id 
        WHERE i.quantity_available < 10 
        ORDER BY i.quantity_available ASC";
$alerts = $db->query($sql)->fetchAll();
?>

<div class="mb-6 border-b pb-4">
    <h1 class="text-2xl font-bold text-red-600 flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i> Low Stock Alerts
    </h1>
    <p class="text-gray-600 mt-1">Items below threshold that need immediate restocking.</p>
</div>

<?php if(empty($alerts)): ?>
    <div class="bg-green-50 p-6 rounded text-center text-green-700">
        <i class="fas fa-check-circle text-2xl mb-2"></i>
        <p>All inventory levels are healthy.</p>
        <a href="stock.php" class="text-sm font-bold underline mt-2 inline-block">View Stock</a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($alerts as $item): ?>
        <div class="bg-white border-l-4 border-red-500 rounded shadow p-4">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                <span class="text-red-600 font-bold text-xl"><?php echo floatval($item['quantity_available']); ?> <span class="text-sm text-gray-400"><?php echo $item['unit']; ?></span></span>
            </div>
            <p class="text-sm text-gray-500 mb-4"><i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($item['farm_name']); ?></p>
            
            <button onclick="openTxnModal('<?php echo $item['inv_id']; ?>', '<?php echo htmlspecialchars($item['item_name']); ?>')" 
               class="w-full block text-center bg-red-50 text-red-700 py-2 rounded hover:bg-red-100 font-bold text-sm">
               Restock Now
            </button>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    function openTxnModal(id, name) {
        // You would typically redirect or open the modal here.
        // For simplicity, link back to stock with query param to open modal?
        window.location.href = "stock.php?restock=" + id;
    }
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>