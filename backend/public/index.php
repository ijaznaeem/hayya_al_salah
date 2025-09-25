<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Router;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set CORS headers first, before any other output
$corsOrigin = $_ENV['CORS_ORIGINS'] ?? 'http://localhost:4201';
$corsMethods = $_ENV['CORS_METHODS'] ?? 'GET, POST, PUT, DELETE, OPTIONS';
$corsHeaders = $_ENV['CORS_HEADERS'] ?? 'Content-Type, Authorization, X-Requested-With';

header("Access-Control-Allow-Origin: $corsOrigin");
header("Access-Control-Allow-Methods: $corsMethods");
header("Access-Control-Allow-Headers: $corsHeaders");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set error reporting based on environment
if ($_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Get request path
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Handle file uploads and serving
if (preg_match('#^/uploads/(.+)$#', $requestPath, $matches)) {
    $relativePath = $matches[1];
    $filePath = __DIR__ . '/../storage/uploads/' . $relativePath;
    
    // Normalize the path and check if file exists
    $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);
    
    // Security check - ensure the relative path doesn't contain ../ or other dangerous patterns
    if (strpos($relativePath, '..') !== false || strpos($relativePath, '\\') !== false) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Additional security check - ensure file is within uploads directory
        $realFilePath = realpath($filePath);
        $realUploadsPath = realpath(__DIR__ . '/../storage/uploads');
        
        if ($realFilePath && $realUploadsPath && strpos($realFilePath, $realUploadsPath) === 0) {
            // Determine content type
            $mimeType = mime_content_type($filePath);
            
            // Set headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
            header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
            
            // Output file
            readfile($filePath);
            exit;
        }
    }
    
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Handle admin panel routes
if (strpos($requestPath, '/admin') === 0) {
    // Serve admin panel
    if ($requestPath === '/admin' || $requestPath === '/admin/') {
        header('Location: /admin/index.html');
        exit;
    }
    
    $adminFile = __DIR__ . $requestPath;
    if (file_exists($adminFile) && is_file($adminFile)) {
        $ext = pathinfo($adminFile, PATHINFO_EXTENSION);
        switch ($ext) {
            case 'html':
                header('Content-Type: text/html');
                break;
            case 'css':
                header('Content-Type: text/css');
                break;
            case 'js':
                header('Content-Type: application/javascript');
                break;
            default:
                header('Content-Type: text/plain');
        }
        readfile($adminFile);
        exit;
    }
}

// Handle API routes
if (strpos($requestPath, '/api') === 0) {
    // Set content type for API responses
    header('Content-Type: application/json');

    // Remove /api prefix
    $path = substr($requestPath, 4);
    $method = $_SERVER['REQUEST_METHOD'];

    // Initialize router and handle request
    $router = new Router();
    $router->route($method, $path);
} else {
    // Default route - serve a simple welcome message or redirect to admin
    header('Content-Type: text/html');
    echo '<!DOCTYPE html>
<html>
<head>
    <title>Hayya Al Salah API</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        .container { max-width: 600px; margin: 0 auto; }
        a { color: #007cba; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hayya Al Salah API</h1>
        <p>Welcome to the Hayya Al Salah Islamic Learning Platform API.</p>
        <p><a href="/admin">Go to Admin Panel</a></p>
        <p><a href="/api/topics">View Topics API</a></p>
        <p><a href="/api/lectures">View Lectures API</a></p>
    </div>
</body>
</html>';
}

// Route the request
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query parameters and base path
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/api', '', $path);
