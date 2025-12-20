<?php
// features/files/server.php - Handles file operations

require_once __DIR__ . '/../../core/storage.php';
require_once __DIR__ . '/../../core/eventbus.php';

class FilesFeature {
    private $storage;
    private $eventBus;
    
    public function __construct() {
        $this->storage = new Storage();
        $this->eventBus = new EventBus();
    }
    
    // Handle file upload
    public function uploadFile($roomId, $username) {
        // Validate room exists
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'error' => 'No file uploaded or upload error'
            );
        }
        
        $file = $_FILES['file'];
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileTmpPath = $file['tmp_name'];
        
        // Validate file size (50MB max)
        $maxSize = 50 * 1024 * 1024; // 50MB
        if ($fileSize > $maxSize) {
            return array(
                'success' => false,
                'error' => 'File too large (max 50MB)'
            );
        }
        
        // Create unique filename to avoid conflicts
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $uniqueName = $baseName . '_' . time() . '.' . $fileExt;
        
        // Get room files directory
        $filesDir = __DIR__ . '/../../data/rooms/' . $roomId . '/files/';
        $destination = $filesDir . $uniqueName;
        
        // Move uploaded file
        if (!move_uploaded_file($fileTmpPath, $destination)) {
            return array(
                'success' => false,
                'error' => 'Failed to save file'
            );
        }
        
        // Create file metadata
        $fileInfo = array(
            'id' => uniqid(),
            'originalName' => $fileName,
            'storedName' => $uniqueName,
            'size' => $fileSize,
            'uploadedBy' => $username,
            'uploadedAt' => time()
        );
        
        // Get current room state
        $state = $this->storage->getRoomState($roomId);
        
        // Add file to room state
        if (!isset($state['files'])) {
            $state['files'] = array();
        }
        $state['files'][] = $fileInfo;
        
        // Update room state
        $this->storage->updateRoomState($roomId, array(
            'files' => $state['files']
        ));
        
        // Broadcast file_uploaded event
        $this->eventBus->emit($roomId, 'file_uploaded', $fileInfo);
        
        return array(
            'success' => true,
            'file' => $fileInfo
        );
    }
    
    // Get list of files in room
    public function getFiles($roomId) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        $state = $this->storage->getRoomState($roomId);
        $files = isset($state['files']) ? $state['files'] : array();
        
        return array(
            'success' => true,
            'files' => $files
        );
    }
    
    // Download a file
    public function downloadFile($roomId, $fileId) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Get room state
        $state = $this->storage->getRoomState($roomId);
        $files = isset($state['files']) ? $state['files'] : array();
        
        // Find file by ID
        $fileInfo = null;
        foreach ($files as $file) {
            if ($file['id'] === $fileId) {
                $fileInfo = $file;
                break;
            }
        }
        
        if (!$fileInfo) {
            return array(
                'success' => false,
                'error' => 'File not found'
            );
        }
        
        // Get file path
        $filePath = __DIR__ . '/../../data/rooms/' . $roomId . '/files/' . $fileInfo['storedName'];
        
        if (!file_exists($filePath)) {
            return array(
                'success' => false,
                'error' => 'File does not exist'
            );
        }
        
        // Return file info for download
        return array(
            'success' => true,
            'path' => $filePath,
            'name' => $fileInfo['originalName'],
            'size' => $fileInfo['size']
        );
    }
    
    // Delete a file
    public function deleteFile($roomId, $fileId, $username) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Get room state
        $state = $this->storage->getRoomState($roomId);
        $files = isset($state['files']) ? $state['files'] : array();
        
        // Find and remove file
        $fileInfo = null;
        $newFiles = array();
        foreach ($files as $file) {
            if ($file['id'] === $fileId) {
                $fileInfo = $file;
            } else {
                $newFiles[] = $file;
            }
        }
        
        if (!$fileInfo) {
            return array(
                'success' => false,
                'error' => 'File not found'
            );
        }
        
        // Delete physical file
        $filePath = __DIR__ . '/../../data/rooms/' . $roomId . '/files/' . $fileInfo['storedName'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Update room state
        $this->storage->updateRoomState($roomId, array(
            'files' => $newFiles
        ));
        
        // Broadcast file_deleted event
        $this->eventBus->emit($roomId, 'file_deleted', array(
            'fileId' => $fileId,
            'fileName' => $fileInfo['originalName'],
            'deletedBy' => $username
        ));
        
        return array(
            'success' => true,
            'message' => 'File deleted'
        );
    }
}