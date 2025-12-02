<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $data = [
        'name'     => Validator::sanitize($_POST['item_name']),
        'category' => $_POST['category'],
        'batch'    => Validator::sanitize($_POST['batch_number']),
        'qty'      => floatval($_POST['quantity']),
        'unit'     => Validator::sanitize($_POST['unit']),
        'expiry'   => $_POST['expiry_date']
    ];
    $farm_id = $_POST['farm_id'];

    $invModel = new Inventory($db);
    $invModel->createItem($farm_id, $data);

    Session::set('flash_success', 'Item added to inventory.');
    header("Location: " . BASE_URL . "inventory.php");
    exit;
}
?>