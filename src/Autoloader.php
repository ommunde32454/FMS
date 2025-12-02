<?php
// src/Autoloader.php

spl_autoload_register(function ($class) {
    // 1. Check root of src/ (e.g., src/Database.php)
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // 2. Check src/Models/ (e.g., src/Models/User.php)
    $modelFile = __DIR__ . '/Models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }
});