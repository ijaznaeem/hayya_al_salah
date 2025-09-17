<?php

echo "=== File Upload & Retrieval Test ===\n\n";

// Check upload directories
$uploadDirs = [
    'videos' => __DIR__ . '/storage/uploads/videos',
    'images' => __DIR__ . '/storage/uploads/images',
    'documents' => __DIR__ . '/storage/uploads/documents'
];

echo "1. Checking upload directories:\n";
foreach ($uploadDirs as $type => $dir) {
    echo "   - $type: " . ($dir) . "\n";
    echo "     Exists: " . (is_dir($dir) ? '✓' : '✗') . "\n";
    echo "     Writable: " . (is_writable($dir) ? '✓' : '✗') . "\n";
}

echo "\n2. Creating test files:\n";

// Create a test image (simple PNG)
$testImagePath = __DIR__ . '/test_image.png';
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jINuGwAAAABJRU5ErkJggg==');
file_put_contents($testImagePath, $imageData);
echo "   - Test image created: $testImagePath\n";

// Create a test document (simple text file for now)
$testDocPath = __DIR__ . '/test_document.txt';
file_put_contents($testDocPath, "This is a test document for upload testing.\nCreated at: " . date('Y-m-d H:i:s'));
echo "   - Test document created: $testDocPath\n";

echo "\n3. Testing file uploads:\n";

// Function to test upload
function testUpload($filePath, $uploadType, $token) {
    if (!file_exists($filePath)) {
        echo "   ✗ File not found: $filePath\n";
        return false;
    }
    
    $url = "http://localhost:8000/api/upload/$uploadType";
    
    $postFields = [
        'file' => new CURLFile($filePath)
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
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
    
    $result = json_decode($response, true);
    
    echo "   - Upload $uploadType (HTTP $httpCode):\n";
    if ($httpCode === 200 && isset($result['success']) && $result['success']) {
        echo "     ✓ Upload successful\n";
        echo "     URL: {$result['url']}\n";
        return $result['url'];
    } else {
        echo "     ✗ Upload failed\n";
        echo "     Response: $response\n";
        return false;
    }
}

// Function to test file retrieval
function testRetrieval($url) {
    $fullUrl = "http://localhost:8000$url";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true, // HEAD request
        CURLOPT_HEADER => true
    ]);
    
    $headers = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   - Retrieval test for $url (HTTP $httpCode):\n";
    if ($httpCode === 200) {
        echo "     ✓ File accessible\n";
        return true;
    } else {
        echo "     ✗ File not accessible\n";
        echo "     Headers: $headers\n";
        return false;
    }
}

// First, let's get an admin token
echo "\n4. Getting admin authentication token:\n";

$loginData = [
    'email' => 'admin@hayyaalsalah.com',
    'password' => 'admin123'
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/auth/login',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($loginData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$loginResult = json_decode($response, true);

if ($httpCode === 200 && isset($loginResult['token'])) {
    $token = $loginResult['token'];
    echo "   ✓ Authentication successful\n";
    echo "   Token: " . substr($token, 0, 20) . "...\n";
    
    echo "\n5. Testing uploads:\n";
    
    // Test image upload
    $imageUrl = testUpload($testImagePath, 'image', $token);
    
    // Test document upload (temporarily allow text files)
    $docUrl = testUpload($testDocPath, 'document', $token);
    
    echo "\n6. Testing file retrieval:\n";
    
    if ($imageUrl) {
        testRetrieval($imageUrl);
    }
    
    if ($docUrl) {
        testRetrieval($docUrl);
    }
    
    echo "\n7. Checking actual file creation:\n";
    
    if ($imageUrl) {
        $actualImagePath = __DIR__ . '/storage' . $imageUrl;
        echo "   - Image file: " . (file_exists($actualImagePath) ? '✓ Exists' : '✗ Missing') . " at $actualImagePath\n";
    }
    
    if ($docUrl) {
        $actualDocPath = __DIR__ . '/storage' . $docUrl;
        echo "   - Document file: " . (file_exists($actualDocPath) ? '✓ Exists' : '✗ Missing') . " at $actualDocPath\n";
    }
    
} else {
    echo "   ✗ Authentication failed\n";
    echo "   Response: $response\n";
}

// Cleanup test files
echo "\n8. Cleaning up test files:\n";
if (file_exists($testImagePath)) {
    unlink($testImagePath);
    echo "   ✓ Test image removed\n";
}
if (file_exists($testDocPath)) {
    unlink($testDocPath);
    echo "   ✓ Test document removed\n";
}

echo "\n=== Test Complete ===\n";
