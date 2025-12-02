<?php
// src/Validator.php
class Validator {
    
    // Sanitize String
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    // Validate Date (YYYY-MM-DD)
    public static function isDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // Validate Required Fields
    public static function required($fields, $data) {
        foreach ($fields as $field) {
            if (empty($data[$field])) return false;
        }
        return true;
    }
}
?>