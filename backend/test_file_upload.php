<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== File Upload Test ===\n\n";

// First, let's get an admin token
echo "1. Getting admin token...\n";

$loginData = [
    'email' => 'admin@hayyaalsalah.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Failed to login. HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResponse = json_decode($response, true);
if (!isset($loginResponse['token'])) {
    echo "❌ No token in login response\n";
    echo "Response: $response\n";
    exit(1);
}

$token = $loginResponse['token'];
echo "✅ Admin token obtained successfully\n\n";

// Now test document upload
echo "2. Testing document upload...\n";

$testFile = __DIR__ . '/test_document.txt';
if (!file_exists($testFile)) {
    echo "❌ Test file not found: $testFile\n";
    exit(1);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/upload/document');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile($testFile, 'text/plain', 'test_document.txt')
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$uploadResponse = curl_exec($ch);
$uploadHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Upload HTTP Code: $uploadHttpCode\n";
echo "Upload Response: $uploadResponse\n\n";

$uploadResult = json_decode($uploadResponse, true);

if ($uploadHttpCode === 200 && isset($uploadResult['success']) && $uploadResult['success']) {
    echo "✅ File uploaded successfully!\n";
    echo "File URL: " . $uploadResult['url'] . "\n";
    echo "Original name: " . $uploadResult['original_name'] . "\n";
    echo "File size: " . $uploadResult['size'] . " bytes\n\n";
    
    // Test file retrieval
    echo "3. Testing file retrieval...\n";
    $fileUrl = 'http://localhost:8000' . $uploadResult['url'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $fileResponse = curl_exec($ch);
    $fileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "File retrieval HTTP Code: $fileHttpCode\n";
    
    if ($fileHttpCode === 200) {
        echo "✅ File retrieved successfully!\n";
        
        // Extract headers and content
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($fileResponse, 0, $headerSize);
        $content = substr($fileResponse, $headerSize);
        
        echo "File content preview (first 100 chars):\n";
        echo substr($content, 0, 100) . "...\n\n";
        
        // Check if file exists in storage
        $localFilePath = __DIR__ . '/storage' . $uploadResult['url'];
        echo "4. Checking local file storage...\n";
        echo "Local file path: $localFilePath\n";
        
        if (file_exists($localFilePath)) {
            echo "✅ File exists in local storage\n";
            echo "File size on disk: " . filesize($localFilePath) . " bytes\n";
        } else {
            echo "❌ File not found in local storage\n";
        }
        
    } else {
        echo "❌ Failed to retrieve file. HTTP Code: $fileHttpCode\n";
    }
    
} else {
    echo "❌ File upload failed\n";
    if (isset($uploadResult['error'])) {
        echo "Error: " . $uploadResult['error'] . "\n";
    }
}

echo "\n=== Test Complete ===\n";
