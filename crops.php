<?php
// crops.php (Router)

$view = $_GET['view'] ?? 'active';

switch($view) {
    case 'calendar':
        require_once __DIR__ . '/views/crops/calendar.php';
        break;
    case 'types':
        require_once __DIR__ . '/views/crops/types.php';
        break;
    case 'history':  // NEW CASE ADDED
        require_once __DIR__ . '/views/crops/history.php';
        break;
    default:
        require_once __DIR__ . '/views/crops/active.php';
        break;
}
?>