<?php
// src/Search.php
class Search {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Powerful Search: Looks across Farms, Owners, and Documents
     */
    public function globalSearch($query) {
        $term = "%$query%";
        $results = [];

        // 1. Search Farms & Owners
        $sqlFarms = "SELECT f.farm_id, f.farm_name, f.survey_number, o.display_name as owner 
                     FROM farms f 
                     JOIN owners o ON f.owner_id = o.owner_id 
                     WHERE f.farm_name LIKE ? OR f.survey_number LIKE ? OR o.display_name LIKE ?";
        $stmt = $this->pdo->prepare($sqlFarms);
        $stmt->execute([$term, $term, $term]);
        $results['farms'] = $stmt->fetchAll();

        // 2. Search Documents
        $sqlDocs = "SELECT d.doc_id, d.doc_number, d.doc_type, f.farm_name 
                    FROM farm_documents d
                    JOIN farms f ON d.farm_id = f.farm_id
                    WHERE d.doc_number LIKE ?";
        $stmt2 = $this->pdo->prepare($sqlDocs);
        $stmt2->execute([$term]);
        $results['documents'] = $stmt2->fetchAll();

        return $results;
    }
}
?>