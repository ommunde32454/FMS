<?php
// src/Auth.php

class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // 1. Login Logic
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT user_id, name, password_hash, role, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] !== 'active') return "Account is " . $user['status'];

            // Prevent session fixation
            session_regenerate_id(true);

            // Store user data in Session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            return true;
        }
        return "Invalid email or password.";
    }

    // 2. Logout Logic
    public function logout() {
        $_SESSION = [];
        session_destroy();
    }

    // 3. Check if Logged In
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // 4. Force Login (Redirect if not logged in) -> THIS WAS MISSING
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Use constant if defined, otherwise relative path
            $url = defined('BASE_URL') ? BASE_URL . 'index.php' : '../index.php';
            header("Location: $url");
            exit;
        }
    }

    // 5. Force Role (Redirect if wrong permission)
    public function requireRole($allowedRoles = []) {
        $this->requireLogin();
        
        $currentRole = $_SESSION['role'] ?? 'guest';
        
        // Convert single string to array if needed
        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array($currentRole, $allowedRoles)) {
            http_response_code(403);
            die("⛔ Access Denied: You do not have permission to view this page.");
        }
    }

    // Helper: Get Current User ID
    public function id() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>