<?php

// Set CORS headers for all requests
header("Access-Control-Allow-Origin: http://localhost:4201");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple router for root directory
$request_uri = $_SERVER['REQUEST_URI'];

if (strpos($request_uri, '/api') === 0) {
    // Forward API requests to public/index.php
    require_once __DIR__ . '/public/index.php';
} else {
    // Show API documentation or welcome message
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Hayya Al Salah API Server is running!',
        'version' => '1.0.0',
        'documentation' => '/api/docs',
        'endpoints' => [
            'topics' => '/api/topics',
            'lectures' => '/api/lectures',
            'auth' => '/api/auth/login'
        ]
    ], JSON_PRETTY_PRINT);
}
