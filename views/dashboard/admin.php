<?php
$db = Database::getInstance()->getConnection();

$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'farms' => $db->query("SELECT COUNT(*) FROM farms WHERE status='active'")->fetchColumn(),
    'issues' => $db->query("SELECT COUNT(*) FROM inventory_items WHERE quantity_available < 10")->fetchColumn()
];
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded shadow border-l-4 border-purple-500 flex items-center">
        <div class="p-4 rounded-full bg-purple-100 text-purple-600 mr-4">
            <i class="fas fa-users text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Total Users</p>
            <h3 class="text-2xl font-bold"><?php echo $stats['users']; ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow border-l-4 border-emerald-500 flex items-center">
        <div class="p-4 rounded-full bg-emerald-100 text-emerald-600 mr-4">
            <i class="fas fa-tractor text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Active Farms</p>
            <h3 class="text-2xl font-bold"><?php echo $stats['farms']; ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow border-l-4 border-red-500 flex items-center">
        <div class="p-4 rounded-full bg-red-100 text-red-600 mr-4">
            <i class="fas fa-bell text-2xl"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm">Low Stock Alerts</p>
            <h3 class="text-2xl font-bold"><?php echo $stats['issues']; ?></h3>
        </div>
    </div>
</div>