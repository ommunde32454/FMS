<?php
// views/admin/audit_logs.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireRole(['superadmin']);

require_once __DIR__ . '/../../templates/header.php';

// Fetch Logs
$sql = "SELECT a.*, u.name as user_name, u.email 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.created_at DESC 
        LIMIT 100";
$logs = $db->query($sql)->fetchAll();
?>

<div class="flex min-h-screen">
    <?php include __DIR__ . '/../../templates/sidebar.php'; ?>

    <div class="flex-grow p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">System Audit Logs</h1>
            <p class="text-gray-500 text-sm">Tracking the last 100 system actions.</p>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-bold">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Action</th>
                        <th class="px-6 py-3">Target</th>
                        <th class="px-6 py-3">Details (JSON)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($logs as $log): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            <?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <?php echo htmlspecialchars($log['user_name'] ?? 'System/Deleted User'); ?>
                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($log['email'] ?? ''); ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($log['action_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <?php echo htmlspecialchars($log['table_affected']); ?>
                            <span class="text-xs text-gray-400 block">ID: <?php echo substr($log['record_id'], 0, 8); ?>...</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-mono text-gray-500 max-w-xs truncate" title="<?php echo htmlspecialchars($log['new_value']); ?>">
                            <?php 
                                // Pretty print JSON if possible, or just show text
                                $json = json_decode($log['new_value'], true);
                                echo $json ? htmlspecialchars(json_encode($json)) : htmlspecialchars($log['new_value']); 
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>