<?php
// config/config.php

// 1. App Settings
define('APP_NAME', 'Farm Management System');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Kolkata'); // Change as per your region

// 2. Base URL (Update this if you deploy to a live server)
// Automatically detects http/https and host
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . "://" . $host . "/fms/"); 

// 3. File Upload Paths (Absolute Paths)
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('PROOF_PATH', UPLOAD_PATH . 'proofs/');
define('AGREEMENT_PATH', UPLOAD_PATH . 'agreements/');
define('PHOTO_PATH', UPLOAD_PATH . 'photos/');

// 4. Set Timezone
date_default_timezone_set(TIMEZONE);

// 5. Error Reporting (Turn off for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>