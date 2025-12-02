<?php
// src/Models/Agreement.php

class Agreement {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $uuid = generate_uuid();
        $sql = "INSERT INTO agreements (agreement_id, farm_id, owner_id, start_date, end_date, terms_summary, file_path, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $uuid, $data['farm_id'], $data['owner_id'], 
            $data['start'], $data['end'], $data['terms'], $data['file']
        ]);
        return $uuid;
    }

    public function getByFarm($farmId) {
        $stmt = $this->pdo->prepare("SELECT * FROM agreements WHERE farm_id = ?");
        $stmt->execute([$farmId]);
        return $stmt->fetchAll();
    }
}
?>