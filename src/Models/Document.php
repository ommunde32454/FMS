<?php
// src/Models/Document.php

class Document {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function upload($farmId, $type, $path, $docNumber = '') {
        $uuid = generate_uuid();
        $sql = "INSERT INTO farm_documents (doc_id, farm_id, doc_type, file_path, doc_number) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uuid, $farmId, $type, $path, $docNumber]);
        return $uuid;
    }

    public function getByFarm($farmId) {
        $stmt = $this->pdo->prepare("SELECT * FROM farm_documents WHERE farm_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$farmId]);
        return $stmt->fetchAll();
    }
}
?>