<?php
// src/Models/Plot.php

class Plot {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Add a new plot to a farm
    public function add($farmId, $name, $area, $soilNotes) {
        $uuid = generate_uuid();
        $sql = "INSERT INTO plots (plot_id, farm_id, plot_name, area_sqm, soil_health_notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uuid, $farmId, $name, $area, $soilNotes]);
        return $uuid;
    }

    // Get all plots belonging to a specific farm
    public function getByFarm($farmId) {
        $stmt = $this->pdo->prepare("SELECT * FROM plots WHERE farm_id = ? ORDER BY plot_name ASC");
        $stmt->execute([$farmId]);
        return $stmt->fetchAll();
    }
}
?>