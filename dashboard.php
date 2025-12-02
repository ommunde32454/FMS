<?php
// dashboard.php

// 1. Initialize System
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Autoloader.php';

// 2. Start Session & Security Check
Session::start();
$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->requireLogin(); // Redirects to index.php if not logged in

// 3. Get User Role
$role = Session::get('role');
$pageTitle = "Dashboard";

// 4. Load Header
include __DIR__ . '/templates/header.php';
?>

<div class="flex min-h-screen">
    
    <?php if($role === 'superadmin'): ?>
        <?php include __DIR__ . '/templates/sidebar.php'; ?>
    <?php endif; ?>

    <div class="flex-grow p-6">
        
        <?php include __DIR__ . '/templates/alerts.php'; ?>

        <?php
            // 5. Dynamic View Loading based on Role
            switch ($role) {
                case 'superadmin':
                    // Load the Admin Dashboard View
                    if (file_exists(__DIR__ . '/views/dashboard/admin.php')) {
                        include __DIR__ . '/views/dashboard/admin.php';
                    } else {
                        echo "<div class='text-red-500'>Error: Admin view file not found.</div>";
                    }
                    break;

                case 'owner':
                    // Load the Owner Portal View
                    if (file_exists(__DIR__ . '/views/dashboard/owner.php')) {
                        include __DIR__ . '/views/dashboard/owner.php';
                    } else {
                        echo "<div class='text-red-500'>Error: Owner view file not found.</div>";
                    }
                    break;

                case 'manager':
                case 'field_worker': // Workers share the Manager view (or you can create a separate worker.php)
                    // Load the Manager Dashboard View
                    if (file_exists(__DIR__ . '/views/dashboard/manager.php')) {
                        include __DIR__ . '/views/dashboard/manager.php';
                    } else {
                        echo "<div class='text-red-500'>Error: Manager view file not found.</div>";
                    }
                    break;

                default:
                    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4'>
                            <p class='font-bold'>Role Error</p>
                            <p>Your user role ('$role') is not recognized. Please contact support.</p>
                          </div>";
            }
        ?>
    </div>

</div>

<?php 
// 6. Load Footer
include __DIR__ . '/templates/footer.php'; 
?>