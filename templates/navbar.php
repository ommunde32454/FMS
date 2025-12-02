<?php
// templates/navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
$userName = $_SESSION['name'] ?? 'Guest';
?>
<nav class="bg-emerald-700 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            
            <!-- Brand -->
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-leaf text-emerald-300"></i> 
                <span>FMS System</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-6 text-sm font-medium">
                <?php if($isLoggedIn): ?>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="hover:text-emerald-200 transition">Dashboard</a>
                    
                    <!-- Farms/Inventory/Contracts - Only visible to Manager, Owner, Admin -->
                    <?php if (in_array($userRole, ['manager', 'owner', 'superadmin'])): ?>
                        <a href="<?php echo BASE_URL; ?>farms.php" class="hover:text-emerald-200 transition">Farms</a>
                        <a href="<?php echo BASE_URL; ?>inventory.php" class="hover:text-emerald-200 transition">Inventory</a>
                        <a href="<?php echo BASE_URL; ?>agreements.php" class="hover:text-emerald-200 transition">Contracts</a>
                    <?php endif; ?>
                    
                    <!-- Field Worker's Primary View: Crops (Tasks/Plantings) -->
                    <a href="<?php echo BASE_URL; ?>crops.php" class="hover:text-emerald-200 transition flex items-center">
                        <i class="fas fa-seedling mr-1"></i> Crops/Tasks
                    </a>
                    
                    <!-- Admin Only Link -->
                    <?php if($userRole === 'superadmin'): ?>
                        <a href="<?php echo BASE_URL; ?>users.php" class="hover:text-emerald-200 transition text-yellow-300">
                            <i class="fas fa-users-cog mr-1"></i> Users
                        </a>
                    <?php endif; ?>

                    <!-- LIVE SEARCH BAR -->
                    <div class="relative mx-4 hidden md:block">
                        <input type="text" id="globalSearchInput" placeholder="Quick Search..." 
                               class="bg-emerald-800 text-white placeholder-emerald-300 border border-emerald-600 rounded-full px-4 py-1 text-sm focus:outline-none focus:bg-emerald-900 w-64 transition-all">
                        
                        <div id="globalSearchResults" class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-xl z-50 hidden border border-gray-200 text-gray-800">
                            <!-- Results injected by JS -->
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div class="relative group ml-4">
                        <button class="flex items-center gap-2 focus:outline-none">
                            <span class="bg-emerald-900 px-3 py-1 rounded-full border border-emerald-600"><?php echo htmlspecialchars($userName); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl py-2 hidden group-hover:block text-gray-800 border">
                            <div class="px-4 py-2 border-b text-xs text-gray-500 uppercase">
                                Role: <?php echo $userRole; ?>
                            </div>
                            <a href="<?php echo BASE_URL; ?>logout.php" class="block px-4 py-2 hover:bg-red-50 text-red-600">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>index.php" class="hover:text-emerald-200">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>