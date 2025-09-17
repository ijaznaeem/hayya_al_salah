<?php

echo "=== File Serving Debug Test ===\n\n";

// Test file path that we know exists
$testUrl = '/uploads/images/68c7ca06814f6_1757923846.png';
$requestPath = parse_url($testUrl, PHP_URL_PATH);

echo "1. Testing file serving logic:\n";
echo "   Request path: $requestPath\n";

// Test the regex pattern
if (preg_match('#^/uploads/(.+)$#', $requestPath, $matches)) {
    echo "   ✓ Regex match successful\n";
    echo "   Matched path: {$matches[1]}\n";
    
    $relativePath = $matches[1];
    $filePath = __DIR__ . '/../storage/uploads/' . $relativePath;
    
    echo "   Constructed file path: $filePath\n";
    echo "   Normalized path: " . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath) . "\n";
    
    // Security check
    if (strpos($relativePath, '..') !== false || strpos($relativePath, '\\') !== false) {
        echo "   ✗ Security check failed\n";
    } else {
        echo "   ✓ Security check passed\n";
    }
    
    // File existence check
    if (file_exists($filePath) && is_file($filePath)) {
        echo "   ✓ File exists and is a file\n";
        echo "   File size: " . filesize($filePath) . " bytes\n";
        echo "   MIME type: " . mime_content_type($filePath) . "\n";
        
        // Realpath checks
        $realFilePath = realpath($filePath);
        $realUploadsPath = realpath(__DIR__ . '/../storage/uploads');
        
        echo "   Real file path: " . ($realFilePath ?: 'NULL') . "\n";
        echo "   Real uploads path: " . ($realUploadsPath ?: 'NULL') . "\n";
        
        if ($realFilePath && $realUploadsPath && strpos($realFilePath, $realUploadsPath) === 0) {
            echo "   ✓ Security path check passed\n";
        } else {
            echo "   ✗ Security path check failed\n";
            echo "     realFilePath: $realFilePath\n";
            echo "     realUploadsPath: $realUploadsPath\n";
            echo "     strpos result: " . (strpos($realFilePath, $realUploadsPath)) . "\n";
        }
    } else {
        echo "   ✗ File does not exist or is not a file\n";
        echo "   file_exists(): " . (file_exists($filePath) ? 'true' : 'false') . "\n";
        echo "   is_file(): " . (is_file($filePath) ? 'true' : 'false') . "\n";
    }
} else {
    echo "   ✗ Regex match failed\n";
}

echo "\n2. Direct file access test:\n";

// Test direct access to a file we know exists
$files = glob(__DIR__ . '/storage/uploads/images/*');
if (!empty($files)) {
    $testFile = $files[0];
    $filename = basename($testFile);
    echo "   Found test file: $filename\n";
    echo "   Full path: $testFile\n";
    echo "   File exists: " . (file_exists($testFile) ? '✓' : '✗') . "\n";
    echo "   Is readable: " . (is_readable($testFile) ? '✓' : '✗') . "\n";
    echo "   File size: " . filesize($testFile) . " bytes\n";
    
    // Test the URL for this file
    $testUrl = "/uploads/images/$filename";
    echo "   Test URL: $testUrl\n";
    
    // Test with cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "http://localhost:8000$testUrl",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HEADER => true
    ]);
    
    $headers = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP response: $httpCode\n";
    if ($httpCode !== 200) {
        echo "   Headers: $headers\n";
    }
} else {
    echo "   No files found in uploads/images/\n";
}

echo "\n=== Debug Complete ===\n";
