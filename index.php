<?php
// index.php

// 1. Initialize System (Correct paths for ROOT directory)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Autoloader.php';

// 2. Start Session
Session::start();

// 3. Check Authentication
$db = Database::getInstance()->getConnection();
$auth = new Auth($db);

if ($auth->isLoggedIn()) {
    // If logged in, go to Dashboard
    header("Location: " . BASE_URL . "dashboard.php");
    exit;
} else {
    // If not logged in, go to Login View
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit;
}
?>