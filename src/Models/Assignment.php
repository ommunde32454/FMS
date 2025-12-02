<?php
// src/Models/Assignment.php

class Assignment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Assigns a worker to a specific plot or farm.
     * target_type can be 'plot' or 'farm'.
     * UPDATED: Accepts relatedPlantingId to link assignment to the specific crop cycle.
     */
    public function assign($userId, $targetType, $targetId, $role, $startDate, $endDate, $notes, $relatedPlantingId = null) {
        $uuid = generate_uuid();
        
        // Deactivate any existing active assignments for this user on this plot
        $this->deactivateExisting($userId, $targetType, $targetId);

        // SQL UPDATED to include related_planting_id
        $sql = "INSERT INTO assignments (assignment_id, user_id, target_type, target_id, role, start_date, end_date, active, notes, related_planting_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uuid, $userId, $targetType, $targetId, $role, $startDate, $endDate, $notes, $relatedPlantingId]);
        
        return $uuid;
    }

    // Get active assignments for a target (e.g., plot)
    public function getActiveByTarget($targetId) {
        $sql = "SELECT a.*, u.name as user_name, u.email 
                FROM assignments a 
                JOIN users u ON a.user_id = u.user_id
                WHERE a.target_id = ? AND a.active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$targetId]);
        return $stmt->fetchAll();
    }

    // Helper to deactivate an existing assignment (used when creating a new assignment)
    private function deactivateExisting($userId, $targetType, $targetId) {
        $sql = "UPDATE assignments SET active = FALSE, end_date = NOW() 
                WHERE user_id = ? AND target_type = ? AND target_id = ? AND active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $targetType, $targetId]);
    }

    // CRITICAL: Fetches the IDs of plots assigned to a user (used by Field Worker view filter)
    public function getActivePlotIdsByUser($userId) {
        $sql = "SELECT target_id FROM assignments 
                WHERE user_id = ? 
                  AND target_type = 'plot' 
                  AND active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        // array_column extracts just the 'target_id' values into a simple array
        return array_column($stmt->fetchAll(), 'target_id');
    }
}
?>