<?php
// views/farms/show.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Farm ID missing.");

$db = Database::getInstance()->getConnection();
$farmModel = new Farm($db);
$farm = $farmModel->getById($id);

if (!$farm) die("Farm not found.");

// Fetch Linked Data
$plotModel = new Plot($db);
$plots = $plotModel->getByFarm($id);

$docModel = new Document($db);
$docs = $docModel->getByFarm($id);

// Safe Coordinates for Map (Defaults to India center if missing)
$lat = !empty($farm['latitude']) ? $farm['latitude'] : 20.5937;
$lng = !empty($farm['longitude']) ? $farm['longitude'] : 78.9629;
?>

<!-- Header -->
<div class="mb-6 flex justify-between items-start">
    <div>
        <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($farm['farm_name']); ?></h1>
        <p class="text-gray-500">
            <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($farm['owner_name'] ?? 'Unknown'); ?> | 
            <i class="fas fa-map-marker-alt mr-1"></i> <?php echo htmlspecialchars($farm['survey_number']); ?>
        </p>
    </div>
    <div class="space-x-2">
        <?php if($_SESSION['role'] !== 'owner'): ?>
            <a href="<?php echo BASE_URL; ?>farm_edit.php?id=<?php echo $id; ?>" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Edit</a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>farms.php" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Back</a>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" role="tablist">
        <li class="mr-2">
            <button onclick="openTab('overview')" class="inline-block p-4 text-emerald-600 border-b-2 border-emerald-600 rounded-t-lg active hover:text-emerald-600" id="overview-tab">Overview</button>
        </li>
        <li class="mr-2">
            <button onclick="openTab('plots')" class="inline-block p-4 text-gray-500 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 rounded-t-lg" id="plots-tab">Plots (<?php echo count($plots); ?>)</button>
        </li>
        <li class="mr-2">
            <button onclick="openTab('docs')" class="inline-block p-4 text-gray-500 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 rounded-t-lg" id="docs-tab">Documents (<?php echo count($docs); ?>)</button>
        </li>
    </ul>
</div>

<!-- Tab Content -->
<div id="tab-content">
    
    <!-- OVERVIEW TAB -->
    <div class="hidden" id="overview" style="display:block;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Details Column -->
            <div class="bg-white p-6 rounded shadow h-full">
                <h3 class="font-bold text-gray-700 mb-4 text-lg border-b pb-2">Farm Details</h3>
                <ul class="space-y-4 text-sm">
                    <li class="flex justify-between items-center">
                        <span class="text-gray-500">Survey Number</span> 
                        <span class="font-mono font-medium bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($farm['survey_number']); ?></span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-gray-500">Total Area</span> 
                        <span class="font-medium text-lg"><?php echo number_format($farm['area_total_sqm'], 2); ?> <small class="text-gray-400">sq m</small></span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-gray-500">Status</span> 
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold uppercase">Active</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-gray-500">GPS Location</span> 
                        <a href="https://www.google.com/maps?q=<?php echo $lat.','.$lng; ?>" target="_blank" class="text-blue-600 hover:underline">
                            <?php echo round($lat, 4) . ', ' . round($lng, 4); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Map Column -->
            <div class="bg-white p-2 rounded shadow h-80 md:h-auto min-h-[300px] border relative">
                <div id="miniMap" class="w-full h-full rounded z-0"></div>
            </div>
        </div>
    </div>

    <!-- PLOTS TAB -->
    <div class="hidden p-4 bg-white rounded-lg shadow-sm" id="plots">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-700">Sub-Plots</h3>
            <?php if($_SESSION['role'] !== 'owner'): ?>
                <button onclick="toggleModal('addPlotModal')" class="bg-emerald-600 text-white px-3 py-1 rounded text-sm">Add Plot</button>
            <?php endif; ?>
        </div>
        
        <?php if(empty($plots)): ?>
            <div class="text-center py-8 text-gray-400 border-2 border-dashed rounded">No plots defined yet.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach($plots as $p): ?>
                <div class="border rounded p-4 hover:bg-gray-50 transition border-l-4 border-emerald-500 bg-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-emerald-800 text-lg"><?php echo htmlspecialchars($p['plot_name']); ?></div>
                            <div class="text-sm text-gray-500 font-mono mt-1"><?php echo $p['area_sqm']; ?> sq m</div>
                        </div>
                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded capitalize"><?php echo $p['status']; ?></span>
                    </div>
                    <?php if(!empty($p['soil_health_notes'])): ?>
                        <div class="text-xs text-gray-500 mt-3 pt-2 border-t border-gray-100 italic">
                            <i class="fas fa-flask mr-1"></i> <?php echo htmlspecialchars($p['soil_health_notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($_SESSION['role'] !== 'owner'): ?>
                        <div class="mt-3 text-right">
                            <a href="<?php echo BASE_URL; ?>views/plots/manage.php?id=<?php echo $p['plot_id']; ?>" class="text-blue-600 text-xs font-bold hover:underline">Manage</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- DOCUMENTS TAB -->
    <div class="hidden p-4 bg-white rounded-lg shadow-sm" id="docs">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-700">Attachments</h3>
            <?php if($_SESSION['role'] !== 'owner'): ?>
                <button onclick="toggleModal('addDocModal')" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Upload</button>
            <?php endif; ?>
        </div>
        <ul class="space-y-2">
            <?php foreach($docs as $d): ?>
            <li class="flex justify-between items-center border p-3 rounded hover:bg-gray-50 bg-white">
                <div class="flex items-center">
                    <div class="bg-red-100 p-2 rounded text-red-600 mr-3">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($d['doc_type']); ?></p>
                        <p class="text-xs text-gray-400">Uploaded: <?php echo date('M d, Y', strtotime($d['uploaded_at'])); ?></p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL . $d['file_path']; ?>" target="_blank" class="text-blue-600 hover:underline text-sm font-medium">
                    Download <i class="fas fa-external-link-alt ml-1"></i>
                </a>
            </li>
            <?php endforeach; ?>
            <?php if(empty($docs)): ?>
                <li class="text-gray-400 text-center py-4">No documents attached.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Modal: Add Plot -->
<div id="addPlotModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 relative">
        <h3 class="font-bold mb-4">Add Plot</h3>
        <form action="<?php echo BASE_URL; ?>controllers/plot_save.php" method="POST">
            <?php echo CSRF::input(); ?>
            <input type="hidden" name="farm_id" value="<?php echo $id; ?>">
            <input type="text" name="plot_name" placeholder="Plot Name (e.g. North Field)" class="w-full border p-2 mb-2 rounded" required>
            <input type="number" name="area" placeholder="Area (sq m)" class="w-full border p-2 mb-2 rounded" required>
            <textarea name="notes" placeholder="Soil Notes" class="w-full border p-2 mb-4 rounded"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleModal('addPlotModal')" class="text-gray-500 px-3 py-2">Cancel</button>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Upload Doc -->
<div id="addDocModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 relative">
        <h3 class="font-bold mb-4">Upload Document</h3>
        <form action="<?php echo BASE_URL; ?>controllers/doc_upload.php" method="POST" enctype="multipart/form-data">
            <?php echo CSRF::input(); ?>
            <input type="hidden" name="farm_id" value="<?php echo $id; ?>">
            <select name="doc_type" class="w-full border p-2 mb-2 rounded">
                <option value="ownership_proof">Ownership Proof</option>
                <option value="survey_map">Survey Map</option>
                <option value="other">Other</option>
            </select>
            <input type="file" name="file" class="w-full border p-2 mb-4 rounded" required>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="toggleModal('addDocModal')" class="text-gray-500 px-3 py-2">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- LEAFLET JS IS REQUIRED HERE -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    function openTab(tabName) {
        ['overview', 'plots', 'docs'].forEach(t => {
            const el = document.getElementById(t);
            const btn = document.getElementById(t + '-tab');
            
            if (t === tabName) {
                el.style.display = 'block';
                btn.className = "inline-block p-4 text-emerald-600 border-b-2 border-emerald-600 rounded-t-lg active";
                
                // Fix map render issue in tabs
                if(tabName === 'overview' && map) {
                    setTimeout(() => { map.invalidateSize(); }, 200);
                }
            } else {
                el.style.display = 'none';
                btn.className = "inline-block p-4 text-gray-500 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 rounded-t-lg";
            }
        });
    }

    // Initialize Map
    var map;
    document.addEventListener('DOMContentLoaded', function() {
        // Only init if element exists
        if(document.getElementById('miniMap')) {
            map = L.map('miniMap').setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map);
        }
    });
</script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>