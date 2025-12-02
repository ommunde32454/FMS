<?php
// src/Models/User.php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create a new user (Admin function)
    public function create($name, $email, $password, $role) {
        $uuid = generate_uuid();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (user_id, name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([$uuid, $name, $email, $hash, $role]);
            return $uuid;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error code
                throw new Exception("Email already exists.");
            }
            throw $e;
        }
    }

    // Get all users for the Admin List
    public function getAll() {
        return $this->pdo->query("SELECT user_id, name, email, role, status, last_login FROM users ORDER BY created_at DESC")->fetchAll();
    }
    
    // Get personnel needed for assignment dropdown (Managers and Field Workers)
    public function getAssignmentPersonnel() {
        $sql = "SELECT user_id, name, role FROM users WHERE role IN ('manager', 'field_worker') AND status = 'active' ORDER BY role, name";
        return $this->pdo->query($sql)->fetchAll();
    }
}
?>