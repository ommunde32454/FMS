<?php
// views/agreements/index.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireLogin();

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// 1. Base Query
$sql = "SELECT a.*, f.farm_name, o.display_name as owner_name 
        FROM agreements a
        JOIN farms f ON a.farm_id = f.farm_id
        JOIN owners o ON a.owner_id = o.owner_id
        WHERE 1=1";
$params = [];

// 2. DATA ISOLATION LOGIC: Filter by owner if the user is a farm owner
if ($role === 'owner') {
    // Get the Owner ID for this login
    $stmtO = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
    $stmtO->execute([$userId]);
    $myOwnerId = $stmtO->fetchColumn();

    if ($myOwnerId) {
        $sql .= " AND a.owner_id = ?";
        $params[] = $myOwnerId;
    } else {
        // Owner profile not fully linked, show nothing
        $sql .= " AND 1=0";
    }
}
// Managers and Super Admins see all (no WHERE clause needed beyond WHERE 1=1)

$sql .= " ORDER BY a.end_date ASC";

// 3. Fetch Data
$stmt = $db->prepare($sql);
$stmt->execute($params);
$agreements = $stmt->fetchAll();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Contract Management</h1>
        <p class="text-gray-500 text-sm">Manage owner agreements and renewals.</p>
    </div>
    <!-- Hide 'New Agreement' button for Owners -->
    <?php if($role !== 'owner'): ?>
        <a href="<?php echo BASE_URL; ?>views/agreements/create.php" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">
            <i class="fas fa-file-signature mr-2"></i> New Agreement
        </a>
    <?php endif; ?>
</div>

<!-- Agreements Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($agreements as $doc): ?>
        <?php 
            // Calculate status logic
            $daysLeft = (strtotime($doc['end_date']) - time()) / (60 * 60 * 24);
            $statusColor = 'border-green-500';
            $statusBadge = 'bg-green-100 text-green-800';
            $statusText = 'Active';

            if ($doc['status'] === 'terminated') {
                $statusColor = 'border-gray-400';
                $statusBadge = 'bg-gray-100 text-gray-600';
                $statusText = 'Terminated';
            } elseif ($daysLeft < 0) {
                $statusColor = 'border-red-500';
                $statusBadge = 'bg-red-100 text-red-800';
                $statusText = 'Expired';
            } elseif ($daysLeft < 30) {
                $statusColor = 'border-yellow-500';
                $statusBadge = 'bg-yellow-100 text-yellow-800';
                $statusText = 'Expiring Soon';
            }
        ?>

        <div class="bg-white rounded-lg shadow-sm border-l-4 <?php echo $statusColor; ?> p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase">Farm</span>
                    <h3 class="font-bold text-lg text-gray-800 leading-tight mb-1">
                        <?php echo htmlspecialchars($doc['farm_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-user-tie mr-1"></i> <?php echo htmlspecialchars($doc['owner_name']); ?>
                    </p>
                </div>
                <span class="<?php echo $statusBadge; ?> text-xs px-2 py-1 rounded-full font-bold">
                    <?php echo $statusText; ?>
                </span>
            </div>

            <div class="bg-gray-50 rounded p-3 text-sm mb-4">
                <div class="flex justify-between mb-1">
                    <span class="text-gray-500">Start:</span>
                    <span class="font-medium"><?php echo date('M d, Y', strtotime($doc['start_date'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">End:</span>
                    <span class="font-medium <?php echo $daysLeft < 30 ? 'text-red-600' : ''; ?>">
                        <?php echo date('M d, Y', strtotime($doc['end_date'])); ?>
                    </span>
                </div>
            </div>

            <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                <a href="<?php echo BASE_URL . $doc['file_path']; ?>" target="_blank" class="text-blue-600 text-sm hover:underline font-medium">
                    <i class="fas fa-download mr-1"></i> Download PDF
                </a>
                <span class="text-xs text-gray-400">
                    ID: <?php echo substr($doc['agreement_id'], 0, 6); ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
    
    <?php if(empty($agreements)): ?>
        <div class="col-span-full py-10 text-center text-gray-500 bg-white rounded border border-dashed">
            <i class="fas fa-file-contract text-4xl mb-3 opacity-30"></i>
            <p>
                <?php echo ($role === 'owner') ? "No agreements found for your properties." : "No agreements found. Create one to get started."; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>