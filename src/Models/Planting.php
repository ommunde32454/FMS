<?php
// src/Models/Planting.php

class Planting {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Record a new planting
    public function plant($plotId, $cropTypeId, $date, $managerId) {
        $uuid = generate_uuid();
        
        // 1. Get cycle days to auto-calculate expected harvest
        $stmt = $this->pdo->prepare("SELECT typical_cycle_days FROM crop_types WHERE crop_type_id = ?");
        $stmt->execute([$cropTypeId]);
        $days = $stmt->fetchColumn();
        
        // Default to 90 days if not found
        $days = $days ? $days : 90; 
        
        $expected = date('Y-m-d', strtotime($date . " + $days days"));

        // 2. Insert Record
        $sql = "INSERT INTO plantings (planting_id, plot_id, crop_type_id, planting_date, expected_harvest_date, status, manager_id) 
                VALUES (?, ?, ?, ?, ?, 'growing', ?)";
        
        $insert = $this->pdo->prepare($sql);
        $insert->execute([$uuid, $plotId, $cropTypeId, $date, $expected, $managerId]);
        return $uuid;
    }

    // Helper to get active plantings for a farm (Optional, as your view uses raw SQL currently)
    public function getActive($farmId) {
        $sql = "SELECT p.*, c.name as crop_name, pl.plot_name 
                FROM plantings p
                JOIN crop_types c ON p.crop_type_id = c.crop_type_id
                JOIN plots pl ON p.plot_id = pl.plot_id
                WHERE pl.farm_id = ? AND p.status IN ('planted', 'growing')
                ORDER BY p.expected_harvest_date ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$farmId]);
        return $stmt->fetchAll();
    }
}
?>