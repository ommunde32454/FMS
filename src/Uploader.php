<?php
// src/Uploader.php
class Uploader {
    
    public static function upload($file, $subFolder, $allowedExts = ['pdf', 'jpg', 'png']) {
        // $subFolder comes from constant, e.g., 'proofs'
        $targetDir = UPLOAD_PATH . $subFolder . '/';
        
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowedExts)) {
            throw new Exception("Invalid file extension.");
        }
        
        if ($file['size'] > 5000000) { // 5MB
            throw new Exception("File too large (Max 5MB).");
        }

        $newName = uniqid('doc_') . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $targetDir . $newName)) {
            // Return relative path for DB
            return 'uploads/' . $subFolder . '/' . $newName;
        }
        throw new Exception("Upload failed.");
    }
}
?>