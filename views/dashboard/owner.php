<?php
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];

// 1. Get Owner ID linked to this User
$stmt = $db->prepare("SELECT owner_id FROM owners WHERE user_id = ?");
$stmt->execute([$userId]);
$ownerId = $stmt->fetchColumn();

if (!$ownerId) {
    echo "<div class='bg-yellow-100 border-l-4 border-yellow-400 p-4 text-yellow-700 rounded'>Profile not linked to an Owner record. Please contact Admin.</div>";
    return;
}

// 2. Stats
$farmCount = $db->prepare("SELECT COUNT(*) FROM farms WHERE owner_id = ? AND status='active'");
$farmCount->execute([$ownerId]);
$totalFarms = $farmCount->fetchColumn();

$agreementCount = $db->prepare("SELECT COUNT(*) FROM agreements WHERE owner_id = ? AND status='active'");
$agreementCount->execute([$ownerId]);
$activeAgreements = $agreementCount->fetchColumn();

// 3. Recent Documents (Proofs/Contracts)
$recentDocs = $db->prepare("
    SELECT d.*, f.farm_name 
    FROM farm_documents d 
    JOIN farms f ON d.farm_id = f.farm_id 
    WHERE f.owner_id = ? 
    ORDER BY d.uploaded_at DESC LIMIT 5
");
$recentDocs->execute([$ownerId]);
$docs = $recentDocs->fetchAll();

// Fetch Owner's Farms for Document Upload Dropdown
$ownerFarms = $db->prepare("SELECT farm_id, farm_name FROM farms WHERE owner_id = ? AND status='active'");
$ownerFarms->execute([$ownerId]);
$myFarms = $ownerFarms->fetchAll();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Owner Portal</h2>
    <p class="text-gray-500">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- My Farms -->
    <div class="bg-white p-6 rounded shadow border-l-4 border-emerald-500 flex justify-between items-center">
        <div>
            <p class="text-gray-500 text-sm font-bold uppercase">My Properties</p>
            <h3 class="text-3xl font-bold text-emerald-600"><?php echo $totalFarms; ?></h3>
            <span class="text-xs text-gray-400">Active Farms</span>
        </div>
        <div class="bg-emerald-100 p-4 rounded-full text-emerald-600">
            <i class="fas fa-map-marked-alt text-2xl"></i>
        </div>
    </div>

    <!-- Agreements -->
    <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500 flex justify-between items-center">
        <div>
            <p class="text-gray-500 text-sm font-bold uppercase">Active Contracts</p>
            <h3 class="text-3xl font-bold text-blue-600"><?php echo $activeAgreements; ?></h3>
            <span class="text-xs text-gray-400">Management Agreements</span>
        </div>
        <div class="bg-blue-100 p-4 rounded-full text-blue-600">
            <i class="fas fa-file-signature text-2xl"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded shadow">
        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Quick Actions</h3>
        <div class="space-y-3">
            <a href="<?php echo BASE_URL; ?>farms.php" class="block bg-gray-50 p-3 rounded hover:bg-emerald-50 hover:text-emerald-700 transition">
                <i class="fas fa-eye mr-2 text-emerald-500"></i> View My Farms (Isolated)
            </a>
            
            <!-- NEW LINK: Upload Document -->
            <button onclick="toggleModal('ownerDocUploadModal')" class="w-full text-left bg-gray-50 p-3 rounded hover:bg-yellow-50 hover:text-yellow-700 transition">
                <i class="fas fa-upload mr-2 text-yellow-500"></i> Upload Land Proofs
            </button>
            
            <!-- NEW LINK: View Map -->
            <a href="<?php echo BASE_URL; ?>views/farms/map.php?owner=<?php echo $ownerId; ?>" class="block bg-gray-50 p-3 rounded hover:bg-blue-50 hover:text-blue-700 transition">
                <i class="fas fa-map mr-2 text-blue-500"></i> View My Land on Map
            </a>
            
            <a href="<?php echo BASE_URL; ?>agreements.php" class="block bg-gray-50 p-3 rounded hover:bg-blue-50 hover:text-blue-700 transition">
                <i class="fas fa-file-contract mr-2 text-blue-500"></i> Review Agreements
            </a>
        </div>
    </div>

    <!-- Recent Documents List -->
    <div class="md:col-span-2 bg-white p-6 rounded shadow">
        <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Recent Documents</h3>
        <?php if(count($docs) > 0): ?>
            <ul class="divide-y divide-gray-100">
                <?php foreach($docs as $doc): ?>
                <li class="py-3 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="bg-gray-100 p-2 rounded mr-3 text-gray-500">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($doc['doc_type']); ?></p>
                            <p class="text-xs text-gray-500">For: <?php echo htmlspecialchars($doc['farm_name']); ?></p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($doc['uploaded_at'])); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-400 text-sm text-center py-4">No documents uploaded recently.</p>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Owner Document Upload -->
<div id="ownerDocUploadModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white w-full max-w-md mx-auto rounded shadow-lg p-6 relative">
        <h3 class="text-lg font-bold mb-4">Upload Land Proofs</h3>
        <form action="<?php echo BASE_URL; ?>controllers/doc_upload.php" method="POST" enctype="multipart/form-data">
            <?php echo CSRF::input(); ?>
            <input type="hidden" name="is_owner_upload" value="1">
            <input type="hidden" name="owner_id" value="<?php echo $ownerId; ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Select Farm</label>
                <select name="farm_id" required class="w-full border rounded p-2 bg-white">
                    <?php if (empty($myFarms)): ?>
                        <option value="">-- No Farms Found --</option>
                    <?php else: ?>
                        <?php foreach ($myFarms as $farm): ?>
                            <option value="<?php echo $farm['farm_id']; ?>"><?php echo htmlspecialchars($farm['farm_name']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Document Type</label>
                <select name="doc_type" required class="w-full border rounded p-2 bg-white">
                    <option value="ownership_proof">Ownership Proof (Required)</option>
                    <option value="survey_map">Survey Map</option>
                    <option value="photo">Farm Photo / Aerial</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">File (PDF/Image)</label>
                <input type="file" name="file" class="w-full border p-2 rounded" required accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleModal('ownerDocUploadModal')" class="text-gray-500 px-3 py-2">Cancel</button>
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Upload Proof</button>
            </div>
        </form>
    </div>
</div>