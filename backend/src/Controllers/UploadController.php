<?php

namespace HayyaAlSalah\Controllers;

class UploadController extends BaseController
{
    private array $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    private array $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    private array $allowedDocumentTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    private int $maxVideoSize = 500 * 1024 * 1024; // 500MB
    private int $maxImageSize = 5 * 1024 * 1024; // 5MB
    private int $maxDocumentSize = 20 * 1024 * 1024; // 20MB

    public function uploadVideo(): void
    {
        $this->requireAdmin();
        $this->handleFileUpload('video', $this->allowedVideoTypes, $this->maxVideoSize, 'videos');
    }

    public function uploadImage(): void
    {
        $this->requireAdmin();
        $this->handleFileUpload('image', $this->allowedImageTypes, $this->maxImageSize, 'images');
    }

    public function uploadDocument(): void
    {
        $this->requireAdmin();
        $this->handleFileUpload('document', $this->allowedDocumentTypes, $this->maxDocumentSize, 'documents');
    }

    private function handleFileUpload(string $type, array $allowedTypes, int $maxSize, string $folder): void
    {
        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->sendResponse(['error' => 'No file uploaded or upload error'], 400);
                return;
            }

            $file = $_FILES['file'];

            // Validate file type
            if (!in_array($file['type'], $allowedTypes)) {
                $this->sendResponse(['error' => "Invalid file type for {$type}"], 400);
                return;
            }

            // Validate file size
            if ($file['size'] > $maxSize) {
                $maxSizeMB = round($maxSize / (1024 * 1024), 2);
                $this->sendResponse(['error' => "File size exceeds {$maxSizeMB}MB limit"], 400);
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . "/../../storage/uploads/{$folder}";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . '/' . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $url = "/uploads/{$folder}/{$filename}";
                
                $this->sendResponse([
                    'success' => true,
                    'url' => $url,
                    'filename' => $filename,
                    'original_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type']
                ]);
            } else {
                $this->sendResponse(['error' => 'Failed to save file'], 500);
            }

        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Upload failed',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function deleteFile(): void
    {
        $this->requireAdmin();
        
        try {
            $input = $this->getRequestData();
            
            if (!isset($input['url'])) {
                $this->sendResponse(['error' => 'File URL is required'], 400);
                return;
            }

            $url = $input['url'];
            
            // Extract file path from URL
            if (strpos($url, '/uploads/') === 0) {
                $filepath = __DIR__ . '/../../storage' . $url;
                
                if (file_exists($filepath)) {
                    if (unlink($filepath)) {
                        $this->sendResponse(['success' => true, 'message' => 'File deleted successfully']);
                    } else {
                        $this->sendResponse(['error' => 'Failed to delete file'], 500);
                    }
                } else {
                    $this->sendResponse(['error' => 'File not found'], 404);
                }
            } else {
                $this->sendResponse(['error' => 'Invalid file URL'], 400);
            }

        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Delete failed',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    public function getUploadedFiles(): void
    {
        $this->requireAdmin();
        
        try {
            $type = $_GET['type'] ?? 'all';
            $page = (int) ($_GET['page'] ?? 1);
            $perPage = (int) ($_GET['per_page'] ?? 20);

            $folders = ['videos', 'images', 'documents'];
            if ($type !== 'all' && in_array($type, $folders)) {
                $folders = [$type];
            }

            $files = [];
            $uploadDir = __DIR__ . '/../../storage/uploads/';

            foreach ($folders as $folder) {
                $folderPath = $uploadDir . $folder;
                if (is_dir($folderPath)) {
                    $folderFiles = scandir($folderPath);
                    foreach ($folderFiles as $file) {
                        if ($file !== '.' && $file !== '..') {
                            $filepath = $folderPath . '/' . $file;
                            $files[] = [
                                'name' => $file,
                                'url' => "/uploads/{$folder}/{$file}",
                                'type' => $folder,
                                'size' => filesize($filepath),
                                'modified' => filemtime($filepath)
                            ];
                        }
                    }
                }
            }

            // Sort by modification time (newest first)
            usort($files, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });

            // Paginate results
            $total = count($files);
            $offset = ($page - 1) * $perPage;
            $paginatedFiles = array_slice($files, $offset, $perPage);

            $this->sendResponse([
                'success' => true,
                'data' => $paginatedFiles,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);

        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Failed to get files',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }
}
