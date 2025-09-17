<?php
// Simple file server test
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Log what we receive
file_put_contents(__DIR__ . '/file_server_log.txt', 
    date('Y-m-d H:i:s') . " - Request: " . $requestPath . "\n", FILE_APPEND);

echo "File Server Test\n";
echo "Request Path: $requestPath\n";

// Handle uploads
if (preg_match('#^/uploads/(.+)$#', $requestPath, $matches)) {
    echo "✓ Upload path matched!\n";
    
    $relativePath = $matches[1];
    $filePath = __DIR__ . '/../storage/uploads/' . $relativePath;
    
    echo "Looking for: $filePath\n";
    
    if (file_exists($filePath) && is_file($filePath)) {
        echo "✓ File found!\n";
        
        // Serve the file
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "✗ File not found\n";
        http_response_code(404);
        exit;
    }
} else {
    echo "✗ Not an upload path\n";
}
