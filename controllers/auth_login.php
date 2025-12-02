<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Check
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
        Session::set('flash_error', 'Invalid Security Token.');
        header("Location: " . BASE_URL . "index.php");
        exit;
    }

    // 2. Process Login
    $db = Database::getInstance()->getConnection();
    $auth = new Auth($db);
    
    $result = $auth->login($_POST['email'], $_POST['password']);

    if ($result === true) {
        header("Location: " . BASE_URL . "dashboard.php");
    } else {
        Session::set('flash_error', $result);
        header("Location: " . BASE_URL . "index.php");
    }
    exit;
}
?>