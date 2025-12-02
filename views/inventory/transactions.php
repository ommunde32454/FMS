<?php
// views/inventory/transactions.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$sql = "SELECT t.*, i.item_name, i.unit, u.name as user_name 
        FROM inventory_transactions t
        JOIN inventory_items i ON t.inv_id = i.inv_id
        LEFT JOIN users u ON t.performed_by = u.user_id
        ORDER BY t.txn_date DESC LIMIT 50";
$logs = $db->query($sql)->fetchAll();
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Transaction History</h1>
    <a href="stock.php" class="text-blue-600 hover:underline text-sm">&larr; Back to Stock</a>
</div>

<div class="bg-white shadow rounded overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead class="bg-gray-100 text-xs font-bold uppercase text-gray-600">
            <tr>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Item</th>
                <th class="px-5 py-3">Type</th>
                <th class="px-5 py-3 text-right">Change</th>
                <th class="px-5 py-3">User</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm">
            <?php foreach($logs as $log): ?>
            <tr class="border-b">
                <td class="px-5 py-3 text-gray-500"><?php echo date('M d, H:i', strtotime($log['txn_date'])); ?></td>
                <td class="px-5 py-3 font-medium"><?php echo htmlspecialchars($log['item_name']); ?></td>
                <td class="px-5 py-3">
                    <span class="uppercase text-xs font-bold px-2 py-1 rounded 
                        <?php echo $log['txn_type'] == 'purchase' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                        <?php echo $log['txn_type']; ?>
                    </span>
                </td>
                <td class="px-5 py-3 text-right font-mono">
                    <?php echo ($log['txn_type'] == 'purchase' ? '+' : '-') . floatval($log['quantity']) . ' ' . $log['unit']; ?>
                </td>
                <td class="px-5 py-3 text-gray-500"><?php echo htmlspecialchars($log['user_name']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>