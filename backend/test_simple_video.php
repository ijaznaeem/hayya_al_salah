<?php

echo "=== Simple Video Upload Test - Lesson1C.mp4 ===\n\n";

$videoFile = __DIR__ . '/Lesson1C.mp4';

// Check if video file exists
if (!file_exists($videoFile)) {
    echo "❌ Video file not found: $videoFile\n";
    exit(1);
}

echo "1. Video file information:\n";
echo "   Path: $videoFile\n";
echo "   Size: " . number_format(filesize($videoFile)) . " bytes (" . round(filesize($videoFile) / 1024 / 1024, 2) . " MB)\n";
echo "   ✓ File exists\n";

// Get admin authentication token
echo "\n2. Getting admin authentication token:\n";

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
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "   ❌ Login failed (HTTP $httpCode)\n";
    echo "   Response: $response\n";
    exit(1);
}

$loginResult = json_decode($response, true);
if (!isset($loginResult['token'])) {
    echo "   ❌ No token in response\n";
    exit(1);
}

$token = $loginResult['token'];
echo "   ✓ Authentication successful\n";

// Upload the video
echo "\n3. Uploading video file:\n";
echo "   Starting upload...\n";

$startTime = microtime(true);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/upload/video',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($videoFile, 'video/mp4', 'Lesson1C.mp4')
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token"
    ],
    CURLOPT_TIMEOUT => 300
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$uploadTime = round(microtime(true) - $startTime, 2);
curl_close($ch);

echo "   Upload completed in {$uploadTime} seconds\n";
echo "   HTTP Code: $httpCode\n";

$uploadResult = json_decode($response, true);

if ($httpCode === 200 && isset($uploadResult['success']) && $uploadResult['success']) {
    echo "   ✅ Upload successful!\n";
    echo "   File URL: {$uploadResult['url']}\n";
    echo "   Filename: {$uploadResult['filename']}\n";
    echo "   Original name: {$uploadResult['original_name']}\n";
    echo "   File size: " . number_format($uploadResult['size']) . " bytes\n";
    echo "   File type: {$uploadResult['type']}\n";
    
    // Test file access
    echo "\n4. Testing video file access:\n";
    $videoUrl = "http://localhost:8000{$uploadResult['url']}";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $videoUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $headers = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Video URL: $videoUrl\n";
    echo "   Access test HTTP Code: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "   ✅ Video file is accessible!\n";
        
        // Check file size from storage
        $actualPath = __DIR__ . '/storage' . $uploadResult['url'];
        if (file_exists($actualPath)) {
            echo "   ✅ File exists in storage\n";
            echo "   Storage path: $actualPath\n";
            echo "   Storage file size: " . number_format(filesize($actualPath)) . " bytes\n";
        }
        
    } else {
        echo "   ❌ Video file is not accessible\n";
    }
    
    echo "\n✅ VIDEO UPLOAD COMPLETE!\n";
    echo "📹 Video URL: $videoUrl\n";
    echo "📁 Ready for use in lectures!\n";
    
} else {
    echo "   ❌ Upload failed\n";
    echo "   Response: $response\n";
    
    if (isset($uploadResult['error'])) {
        echo "   Error: {$uploadResult['error']}\n";
    }
}

echo "\n=== Test Complete ===\n";
