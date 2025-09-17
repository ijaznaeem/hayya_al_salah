<?php
// Router for PHP built-in server

$requestPath = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestPath, PHP_URL_PATH);

// Serve static files directly if they exist
$staticFile = __DIR__ . '/public' . $requestPath;
if (file_exists($staticFile) && is_file($staticFile)) {
    return false; // Let the built-in server handle it
}

// Handle uploads path by routing to index.php
if (preg_match('#^/uploads/(.+)$#', $requestPath)) {
    require_once __DIR__ . '/public/index.php';
    return true;
}

// Route everything else to index.php
require_once __DIR__ . '/public/index.php';
return true;
