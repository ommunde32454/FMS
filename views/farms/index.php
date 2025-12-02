<?php
// views/farms/index.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
$farmModel = new Farm($db);

// Get User Role & ID
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// --- LOGIC: Fetch Farms based on Role ---
if ($role === 'owner') {
    // 1. Get the Owner Profile ID linked to this User
    $stmt = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
    $stmt->execute([$userId]);
    $myOwnerId = $stmt->fetchColumn();

    if ($myOwnerId) {
        // 2. Fetch ONLY farms belonging to this owner
        $farms = $farmModel->getByOwner($myOwnerId);
    } else {
        $farms = []; // No profile linked yet
    }
} else {
    // 3. Admin/Manager: Handle Search or Fetch All
    $query = $_GET['q'] ?? '';
    if ($query) {
        $farms = $farmModel->search($query);
    } else {
        $farms = $farmModel->getAllActive();
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Farm Management</h1>
    
    <div class="flex gap-2">
        <a href="<?php echo BASE_URL; ?>views/farms/map.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            <i class="fas fa-map mr-2"></i> Map View
        </a>
        
        <!-- HIDE "Add Farm" button if user is an Owner -->
        <?php if($role !== 'owner'): ?>
            <a href="<?php echo BASE_URL; ?>views/farms/create.php" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
                <i class="fas fa-plus mr-2"></i> Add Farm
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search Bar (Only show for Admin/Manager) -->
<?php if($role !== 'owner'): ?>
<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET" class="flex gap-2">
        <input type="text" name="q" value="<?php echo htmlspecialchars($query ?? ''); ?>" 
               placeholder="Search by Farm Name, Owner, or Survey No..." 
               class="flex-grow border border-gray-300 p-2 rounded focus:outline-none focus:border-emerald-500">
        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700">Search</button>
        <?php if(!empty($query)): ?>
            <a href="farms.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded flex items-center">Reset</a>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<!-- Farms Table -->
<div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full leading-normal">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
                <th class="py-3 px-6 text-left">Farm Name</th>
                <th class="py-3 px-6 text-left">Owner</th>
                <th class="py-3 px-6 text-left">Survey No</th>
                <th class="py-3 px-6 text-right">Area (sq m)</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm">
            <?php if (count($farms) > 0): ?>
                <?php foreach ($farms as $farm): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <!-- Farm Name Link -->
                    <td class="py-3 px-6 font-medium text-emerald-600">
                        <a href="<?php echo BASE_URL; ?>farm_details.php?id=<?php echo $farm['farm_id']; ?>">
                            <?php echo htmlspecialchars($farm['farm_name']); ?>
                        </a>
                    </td>
                    
                    <!-- Owner Name -->
                    <td class="py-3 px-6">
                        <?php echo htmlspecialchars($farm['owner_name'] ?? 'Unknown'); ?>
                    </td>
                    
                    <!-- Survey No -->
                    <td class="py-3 px-6"><?php echo htmlspecialchars($farm['survey_number']); ?></td>
                    
                    <!-- Area -->
                    <td class="py-3 px-6 text-right"><?php echo number_format($farm['area_total_sqm'], 2); ?></td>
                    
                    <!-- Action Buttons -->
                    <td class="py-3 px-6 text-center">
                        <a href="<?php echo BASE_URL; ?>farm_details.php?id=<?php echo $farm['farm_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        
                        <!-- Show Edit Button ONLY if NOT Owner -->
                        <?php if($role !== 'owner'): ?>
                            <a href="<?php echo BASE_URL; ?>farm_edit.php?id=<?php echo $farm['farm_id']; ?>" class="text-gray-600 hover:text-gray-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-500">
                        <?php echo ($role === 'owner') ? "You have no active farms linked to your account." : "No farms found."; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>