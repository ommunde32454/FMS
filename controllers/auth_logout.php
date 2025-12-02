<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$auth->logout(); // This destroys session

header("Location: " . BASE_URL . "index.php");
exit;
?>