<?php
// src/Models/Transaction.php

class Transaction {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function record($invId, $type, $qty, $userId, $notes) {
        $uuid = generate_uuid();
        $sql = "INSERT INTO inventory_transactions (txn_id, inv_id, txn_type, quantity, performed_by, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uuid, $invId, $type, $qty, $userId, $notes]);
        return $uuid;
    }
}
?>