<?php
echo "=== File Serving Debug ===\n";

$requestPath = "/uploads/documents/68c7c911c7992_1757923601.txt";
echo "Request path: $requestPath\n";

if (preg_match('#^/uploads/(.+)$#', $requestPath, $matches)) {
    echo "Regex matched. Captured: " . $matches[1] . "\n";
    
    $filePath = __DIR__ . '/../storage/uploads/' . $matches[1];
    echo "File path: $filePath\n";
    
    $realPath = realpath($filePath);
    echo "Real path: " . ($realPath ?: 'false') . "\n";
    
    $uploadsPath = realpath(__DIR__ . '/../storage/uploads/');
    echo "Uploads path: " . ($uploadsPath ?: 'false') . "\n";
    
    echo "File exists: " . (file_exists($filePath) ? 'true' : 'false') . "\n";
    
    if ($realPath && $uploadsPath) {
        echo "Path check: " . (strpos($realPath, $uploadsPath) === 0 ? 'true' : 'false') . "\n";
    }
    
    if ($realPath && $uploadsPath && strpos($realPath, $uploadsPath) === 0 && file_exists($realPath)) {
        echo "✅ All checks passed - file should be served\n";
    } else {
        echo "❌ Some check failed\n";
    }
} else {
    echo "Regex did not match\n";
}
