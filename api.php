<?php
// api.php - The AJAX Dispatcher

// 1. Initialize
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Autoloader.php';

// 2. Routing Logic
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_farms':
        require_once __DIR__ . '/api/get_farms_geo.php';
        break;
        
    case 'get_plot':
        require_once __DIR__ . '/api/get_plot_details.php';
        break;
        
    case 'search':
        require_once __DIR__ . '/api/search_global.php';
        break;
        
    default:
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid API Action']);
        break;
}
?>