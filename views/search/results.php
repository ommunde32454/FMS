<?php
// views/search/results.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/Autoloader.php';
require_once __DIR__ . '/../../templates/header.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

$query = $_GET['q'] ?? '';
$results = ['farms' => [], 'documents' => []];

if ($query) {
    // Uses the dedicated Search engine we built in src/Search.php
    $searchEngine = new Search($db);
    $results = $searchEngine->globalSearch($query);
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Global Search</h1>
        <form method="GET" action="results.php" class="relative">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                   placeholder="Search Farms, Owners, Survey Numbers, or Document IDs..." 
                   class="w-full border-2 border-emerald-500 rounded-lg p-4 pl-12 shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 text-lg">
            <i class="fas fa-search absolute left-4 top-5 text-gray-400 text-xl"></i>
            <button type="submit" class="absolute right-3 top-3 bg-emerald-600 text-white px-6 py-2 rounded font-bold hover:bg-emerald-700">
                Find
            </button>
        </form>
    </div>

    <?php if(!$query): ?>
        <div class="text-center py-12 text-gray-400">
            <i class="fas fa-search text-6xl mb-4 opacity-20"></i>
            <p class="text-lg">Enter a keyword to search across the entire system.</p>
        </div>
    <?php else: ?>

        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4 flex items-center">
                <i class="fas fa-tractor text-emerald-500 mr-2"></i> Farms & Properties
                <span class="ml-2 bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?php echo count($results['farms']); ?></span>
            </h2>

            <?php if(empty($results['farms'])): ?>
                <p class="text-gray-500 italic">No farms found matching "<?php echo htmlspecialchars($query); ?>".</p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach($results['farms'] as $f): ?>
                    <a href="<?php echo BASE_URL; ?>views/farms/show.php?id=<?php echo $f['farm_id']; ?>" 
                       class="block bg-white p-4 rounded shadow border-l-4 border-emerald-500 hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-lg text-emerald-700"><?php echo htmlspecialchars($f['farm_name']); ?></h3>
                                <p class="text-sm text-gray-600">
                                    <span class="font-bold">Owner:</span> <?php echo htmlspecialchars($f['owner']); ?> &bull; 
                                    <span class="font-bold">Survey No:</span> <?php echo htmlspecialchars($f['survey_number']); ?>
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-700 border-b pb-2 mb-4 flex items-center">
                <i class="fas fa-file-alt text-blue-500 mr-2"></i> Documents & Proofs
                <span class="ml-2 bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?php echo count($results['documents']); ?></span>
            </h2>

            <?php if(empty($results['documents'])): ?>
                <p class="text-gray-500 italic">No documents found matching "<?php echo htmlspecialchars($query); ?>".</p>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach($results['documents'] as $d): ?>
                    <div class="flex items-center bg-white p-4 rounded shadow border-l-4 border-blue-500">
                        <div class="mr-4 text-blue-500 text-2xl">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($d['doc_type']); ?></h3>
                            <p class="text-sm text-gray-600">
                                Ref: <span class="font-mono bg-gray-100 px-1"><?php echo htmlspecialchars($d['doc_number']); ?></span>
                                &bull; Farm: <?php echo htmlspecialchars($d['farm_name']); ?>
                            </p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>views/farms/show.php?id=<?php echo $d['doc_id']; ?>#docs" 
                           class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded">
                            Locate
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>