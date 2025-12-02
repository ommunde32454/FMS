<?php
// logout.php

// 1. Load Config to get BASE_URL
require_once __DIR__ . '/config/config.php';

// 2. Start Session (if not started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Destroy Session Data
$_SESSION = []; // Empty array
session_unset(); // Free variables
session_destroy(); // Kill session ID

// 4. Redirect to Login
header("Location: " . BASE_URL . "index.php");
exit;
?>