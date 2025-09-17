<?php

echo "=== Direct Upload API Test ===\n\n";

// Create test files
$testImagePath = __DIR__ . '/test_image.png';
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jINuGwAAAABJRU5ErkJggg==');
file_put_contents($testImagePath, $imageData);

echo "1. Test file created: $testImagePath\n";
echo "   MIME type: " . mime_content_type($testImagePath) . "\n";
echo "   Size: " . filesize($testImagePath) . " bytes\n";

// Get authentication token
echo "\n2. Getting authentication token...\n";

$loginData = json_encode([
    'email' => 'admin@hayyaalsalah.com',
    'password' => 'admin123'
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/auth/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $loginData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_VERBOSE => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "   ✗ Login failed (HTTP $httpCode)\n";
    echo "   Response: $response\n";
    exit;
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['token'])) {
    echo "   ✗ No token in response\n";
    echo "   Response: $response\n";
    exit;
}

$token = $loginResult['token'];
echo "   ✓ Authentication successful\n";

// Test upload with detailed debugging
echo "\n3. Testing image upload with debugging...\n";

$postFields = [
    'file' => new CURLFile($testImagePath, 'image/png', 'test_image.png')
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/upload/image',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token"
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
echo "   Response: $response\n";

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if (isset($result['success']) && $result['success']) {
        echo "   ✓ Upload successful!\n";
        echo "   File URL: {$result['url']}\n";
        
        // Test file access
        echo "\n4. Testing file access...\n";
        $fileUrl = "http://localhost:8000{$result['url']}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $fileUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_HEADER => true
        ]);
        
        $headers = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   File access HTTP Code: $httpCode\n";
        if ($httpCode === 200) {
            echo "   ✓ File is accessible\n";
        } else {
            echo "   ✗ File is not accessible\n";
            echo "   Headers: $headers\n";
        }
        
        // Check if file exists on disk
        $actualPath = __DIR__ . '/storage' . $result['url'];
        echo "   File on disk: " . (file_exists($actualPath) ? '✓ Exists' : '✗ Missing') . "\n";
        echo "   Actual path: $actualPath\n";
        
    } else {
        echo "   ✗ Upload failed according to response\n";
    }
} else {
    echo "   ✗ Upload failed\n";
}

// Cleanup
unlink($testImagePath);
echo "\n5. Test file cleaned up\n";

echo "\n=== Test Complete ===\n";
