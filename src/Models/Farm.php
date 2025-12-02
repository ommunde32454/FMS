<?php
// src/Models/Farm.php

class Farm {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $uuid = generate_uuid();
        $sql = "INSERT INTO farms (farm_id, owner_id, farm_name, survey_number, area_total_sqm, latitude, longitude, boundary_polygon) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $uuid, $data['owner_id'], $data['farm_name'], $data['survey_number'],
            $data['area'] ?? 0, $data['lat'] ?? null, $data['lng'] ?? null, $data['polygon'] ?? null
        ]);
        return $uuid;
    }

    // ADMIN/MANAGER: See All
    public function getAllActive() {
        return $this->pdo->query("SELECT f.*, o.display_name as owner_name FROM farms f JOIN owners o ON f.owner_id = o.owner_id WHERE f.status = 'active' ORDER BY f.created_at DESC")->fetchAll();
    }

    // OWNER: See Only Theirs (NEW FUNCTION)
    public function getByOwner($ownerId) {
        $stmt = $this->pdo->prepare("SELECT f.*, o.display_name as owner_name 
                                     FROM farms f 
                                     JOIN owners o ON f.owner_id = o.owner_id 
                                     WHERE f.owner_id = ? AND f.status = 'active' 
                                     ORDER BY f.created_at DESC");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT f.*, o.display_name as owner_name, o.phone, o.city FROM farms f JOIN owners o ON f.owner_id = o.owner_id WHERE f.farm_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($query) {
        $term = "%$query%";
        $sql = "SELECT f.*, o.display_name as owner_name 
                FROM farms f JOIN owners o ON f.owner_id = o.owner_id 
                WHERE f.farm_name LIKE ? OR f.survey_number LIKE ? OR o.display_name LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }
}
?>