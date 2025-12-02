<?php
// src/Audit.php
class Audit {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function log($userId, $action, $table, $recordId, $oldVal = null, $newVal = null) {
        $sql = "INSERT INTO audit_logs (user_id, action_type, table_affected, record_id, old_value, new_value) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $userId, 
            $action, 
            $table, 
            $recordId, 
            $oldVal ? json_encode($oldVal) : null, 
            $newVal ? json_encode($newVal) : null
        ]);
    }
}
?>