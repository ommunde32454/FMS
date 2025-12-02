<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $plot_id = $_POST['plot_id'];
    $crop_id = $_POST['crop_type_id'];
    $date    = $_POST['planting_date'];
    
    $plantingModel = new Planting($db);
    $plantingModel->plant($plot_id, $crop_id, $date, Session::get('user_id'));

    Session::set('flash_success', 'Crop planted successfully.');
    header("Location: " . BASE_URL . "crops.php");
    exit;
}
?>