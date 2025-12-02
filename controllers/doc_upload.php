<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
(new Auth($db))->requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validate($_POST['csrf_token'] ?? '')) die("CSRF Error");

    $farm_id = $_POST['farm_id'] ?? null;
    $type    = $_POST['doc_type'] ?? null;
    $docNumber = $_POST['doc_number'] ?? ''; // Optional field

    // Check if this is an Owner initiated upload from the dashboard
    $isOwnerUpload = isset($_POST['is_owner_upload']) && $_POST['is_owner_upload'] === '1';

    // --- Redirection Destination ---
    if ($isOwnerUpload) {
        $redirectUrl = BASE_URL . "dashboard.php";
    } else {
        // Default redirect for Manager/Admin upload from farm_details page
        $redirectUrl = BASE_URL . "farm_details.php?id=" . $farm_id . "&tab=docs";
    }

    if (!$farm_id || !$type) {
        Session::set('flash_error', 'Missing Farm ID or Document Type.');
        header("Location: " . $redirectUrl);
        exit;
    }
    
    try {
        // 1. Upload File
        $path = Uploader::upload($_FILES['file'], 'proofs');

        // 2. Save to DB
        $docModel = new Document($db);
        $docModel->upload($farm_id, $type, $path, $docNumber);

        Session::set('flash_success', 'Document uploaded successfully.');
    } catch (Exception $e) {
        Session::set('flash_error', $e->getMessage());
    }

    header("Location: " . $redirectUrl);
    exit;
}
?>