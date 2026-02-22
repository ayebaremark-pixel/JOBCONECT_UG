<?php
class FileUploader {
    private $pdo;
    private $uploadPath;
    private $allowedTypes;
    private $maxSize;

    public function __construct($pdo, $uploadPath, $allowedTypes, $maxSize) {
        $this->pdo = $pdo;
        $this->uploadPath = $uploadPath;
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
    }

    public function upload($file, $userId = null) {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            throw new Exception('File too large. Maximum size is ' . ($this->maxSize / 1024 / 1024) . 'MB');
        }

        // Check file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        if (!in_array($mime, $this->allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $this->allowedTypes));
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($userId ? $userId . '_' : '') . '.' . strtolower($extension);
        $destination = $this->uploadPath . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Set proper permissions
        chmod($destination, 0644);

        return $filename;
    }

    public function delete($filename) {
        if (file_exists($this->uploadPath . $filename)) {
            return unlink($this->uploadPath . $filename);
        }
        return false;
    }

    public static function sanitizeFilename($filename) {
        // Remove any characters that aren't letters, numbers, dots or hyphens
        $filename = preg_replace('/[^a-zA-Z0-9\.\-]/', '', $filename);
        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);
        return $filename;
    }
}
?>
