<?php
// src/Models/Inventory.php

class Inventory {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getStock($farmId) {
        $stmt = $this->pdo->prepare("SELECT * FROM inventory_items WHERE farm_id = ? ORDER BY item_name");
        $stmt->execute([$farmId]);
        return $stmt->fetchAll();
    }

    public function createItem($farmId, $data) {
        $uuid = generate_uuid();
        $sql = "INSERT INTO inventory_items (inv_id, farm_id, item_name, category, quantity_available, unit, expiry_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $uuid, $farmId, $data['name'], $data['category'], 
            $data['qty'], $data['unit'], $data['expiry']
        ]);
        return $uuid;
    }
    
    // Updates quantity directly (used by Transaction model)
    public function updateStock($invId, $qty, $operator = '-') {
        $sql = "UPDATE inventory_items SET quantity_available = quantity_available $operator ? WHERE inv_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$qty, $invId]);
    }
}
?>