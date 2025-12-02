<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    try {
        // 1. Handle File Upload
        if (empty($_FILES['contract_file']['name'])) {
            throw new Exception("Signed contract file is required.");
        }
        $filePath = Uploader::upload($_FILES['contract_file'], 'agreements', ['pdf', 'jpg', 'png']);

        // 2. Prepare Data
        $data = [
            'farm_id'  => $_POST['farm_id'],
            'owner_id' => $_POST['owner_id'],
            'start'    => $_POST['start_date'],
            'end'      => $_POST['end_date'],
            'terms'    => Validator::sanitize($_POST['terms']),
            'file'     => $filePath
        ];

        // 3. Save to DB
        $agreeModel = new Agreement($db);
        $agreeModel->create($data);

        Session::set('flash_success', 'Agreement saved successfully.');

    } catch (Exception $e) {
        Session::set('flash_error', $e->getMessage());
    }

    header("Location: " . BASE_URL . "agreements.php");
    exit;
}
?>