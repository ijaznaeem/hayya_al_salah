<?php

// Simple router for root directory
$request_uri = $_SERVER['REQUEST_URI'];

if (strpos($request_uri, '/api') === 0) {
    // Forward API requests to public/index.php
    require_once __DIR__ . '/public/index.php';
} else {
    // Show API documentation or welcome message
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
