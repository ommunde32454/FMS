<?php
// src/Models/Crop.php

class Crop {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all crop types for the list
    public function getAllTypes() {
        return $this->pdo->query("SELECT * FROM crop_types ORDER BY name ASC")->fetchAll();
    }

    // Add a new crop type
    public function addType($name, $days, $desc) {
        $uuid = generate_uuid();
        $stmt = $this->pdo->prepare("INSERT INTO crop_types (crop_type_id, name, typical_cycle_days, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uuid, $name, $days, $desc]);
        return $uuid;
    }
}
?>