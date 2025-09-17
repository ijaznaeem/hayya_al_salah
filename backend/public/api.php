<?php
// Handle serving uploaded files
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string if present
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Check if request is for uploaded files
if (preg_match('#^/uploads/(.+)$#', $requestUri, $matches)) {
    $filePath = __DIR__ . '/../storage/uploads/' . $matches[1];
    
    // Security check - ensure file is within uploads directory
    $realPath = realpath($filePath);
    $uploadsPath = realpath(__DIR__ . '/../storage/uploads/');
    
    if ($realPath && $uploadsPath && strpos($realPath, $uploadsPath) === 0 && file_exists($realPath)) {
        // Determine content type
        $mimeType = mime_content_type($realPath);
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($realPath));
        header('Content-Disposition: inline; filename="' . basename($realPath) . '"');
        
        // Output file
        readfile($realPath);
        exit;
    } else {
        http_response_code(404);
        echo 'File not found';
        exit;
    }
}

// If not a file request, continue with normal API routing
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Router;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set default timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'UTC');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /api prefix if present
if (strpos($path, '/api') === 0) {
    $path = substr($path, 4);
}

// Initialize router and handle request
$router = new Router();
$router->route($method, $path);
