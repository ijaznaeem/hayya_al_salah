<?php

echo "Testing authentication...\n";

// Test login endpoint
$loginData = json_encode([
    'email' => 'admin@hayyaalsalah.com',
    'password' => 'admin123'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "Content-Length: " . strlen($loginData) . "\r\n",
        'content' => $loginData
    ]
]);

echo "Attempting login...\n";
$response = file_get_contents('http://localhost:8000/api/auth/login', false, $context);

if ($response === false) {
    echo "Failed to connect to login endpoint\n";
    exit(1);
}

echo "Login response: $response\n";

$loginResult = json_decode($response, true);
if (isset($loginResult['token'])) {
    $token = $loginResult['token'];
    echo "Login successful! Token: $token\n";
    
    // Now test upload endpoint
    echo "\nTesting upload endpoint...\n";
    
    // Create a test image file
    $testImagePath = sys_get_temp_dir() . '/test_image.png';
    
    // Create a simple 1x1 PNG image
    $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==');
    file_put_contents($testImagePath, $imageData);
    
    echo "Created test image: $testImagePath\n";
    
    // Test upload with curl
    $uploadCommand = "curl -X POST " .
                    "-H \"Authorization: Bearer $token\" " .
                    "-F \"file=@$testImagePath\" " .
                    "http://localhost:8000/api/upload/image";
    
    echo "Upload command: $uploadCommand\n";
    $uploadResult = shell_exec($uploadCommand);
    echo "Upload result: $uploadResult\n";
    
} else {
    echo "Login failed: $response\n";
}

?>
