<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $inv_id = $_POST['inv_id'];
    $type   = $_POST['txn_type']; // 'use', 'purchase', 'loss'
    $qty    = floatval($_POST['quantity']);
    $notes  = Validator::sanitize($_POST['notes']);
    $user_id= Session::get('user_id');

    $invModel = new Inventory($db);
    $txnModel = new Transaction($db);

    try {
        // 1. Update Stock Logic
        $operator = ($type === 'purchase') ? '+' : '-';
        $invModel->updateStock($inv_id, $qty, $operator);

        // 2. Log Transaction
        $txnModel->record($inv_id, $type, $qty, $user_id, $notes);

        Session::set('flash_success', 'Transaction recorded.');
    } catch (Exception $e) {
        Session::set('flash_error', 'Error: ' . $e->getMessage());
    }

    header("Location: " . BASE_URL . "inventory.php");
    exit;
}
?>