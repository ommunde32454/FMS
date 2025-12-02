<?php
// templates/sidebar.php
?>
<aside class="w-full md:w-64 bg-white border-r border-gray-200 hidden md:block min-h-screen">
    <div class="p-6">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Admin Controls</h2>
        
        <nav class="space-y-2">
            <a href="<?php echo BASE_URL; ?>users.php" 
               class="flex items-center px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded transition duration-200">
                <i class="fas fa-users w-6"></i>
                <span class="font-medium">Manage Users</span>
            </a>

            <a href="<?php echo BASE_URL; ?>audit_logs.php" 
               class="flex items-center px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded transition duration-200">
                <i class="fas fa-shield-alt w-6"></i>
                <span class="font-medium">Audit Logs</span>
            </a>

            <a href="<?php echo BASE_URL; ?>crops.php?view=types" 
               class="flex items-center px-4 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 rounded transition duration-200">
                <i class="fas fa-seedling w-6"></i>
                <span class="font-medium">Crop Types</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2 text-gray-400 hover:bg-gray-50 cursor-not-allowed" title="Coming Soon">
                <i class="fas fa-cog w-6"></i>
                <span class="font-medium">Settings</span>
            </a>
        </nav>

        <div class="border-t border-gray-200 my-4"></div>

        <div class="px-4">
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="text-sm text-blue-600 hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</aside>