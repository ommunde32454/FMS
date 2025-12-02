<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $farm_id = $_POST['farm_id'];
    $name    = Validator::sanitize($_POST['plot_name']);
    $area    = floatval($_POST['area']);
    $notes   = Validator::sanitize($_POST['notes']);

    $plotModel = new Plot($db);
    $plotModel->add($farm_id, $name, $area, $notes);

    Session::set('flash_success', 'Plot added successfully.');
    header("Location: " . BASE_URL . "farm_details.php?id=" . $farm_id);
    exit;
}
?>