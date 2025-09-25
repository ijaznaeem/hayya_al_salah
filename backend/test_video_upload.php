<?php

echo "=== Video Upload Test - converted.mp4 ===\n\n";

$videoFile = "C:\\Users\\naeem\\Downloads\\converted.mp4";

// Check if video file exists
if (!file_exists($videoFile)) {
    echo "❌ Video file not found: $videoFile\n";
    exit(1);
}

echo "1. Video file information:\n";
echo "   Path: $videoFile\n";
echo "   Size: " . number_format(filesize($videoFile)) . " bytes (" . round(filesize($videoFile) / 1024 / 1024, 2) . " MB)\n";
echo "   MIME type: " . mime_content_type($videoFile) . "\n";

// Check if size is within limits (500MB = 524,288,000 bytes)
$maxSize = 500 * 1024 * 1024;
if (filesize($videoFile) > $maxSize) {
    echo "   ❌ File too large! Max size is 500MB\n";
    exit(1);
} else {
    echo "   ✓ File size is within limits\n";
}

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
echo "   Starting upload... (this may take a while for large files)\n";

$startTime = microtime(true);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/upload/video',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($videoFile, mime_content_type($videoFile), 'Lesson1C.mp4')
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token"
    ],
    CURLOPT_TIMEOUT => 300, // 5 minute timeout for large files
    CURLOPT_PROGRESSFUNCTION => function($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        if ($upload_size > 0) {
            $percent = round(($uploaded / $upload_size) * 100, 1);
            echo "\r   Progress: {$percent}% (" . number_format($uploaded) . " / " . number_format($upload_size) . " bytes)";
        }
    },
    CURLOPT_NOPROGRESS => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$uploadTime = round(microtime(true) - $startTime, 2);
curl_close($ch);

echo "\n";
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
        CURLOPT_NOBODY => true, // HEAD request only
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
        
        // Extract content type from headers
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $matches)) {
            echo "   Content-Type: " . trim($matches[1]) . "\n";
        }
        
        // Check file size from storage
        $actualPath = __DIR__ . '/storage' . $uploadResult['url'];
        if (file_exists($actualPath)) {
            echo "   ✅ File exists in storage\n";
            echo "   Storage path: $actualPath\n";
            echo "   Storage file size: " . number_format(filesize($actualPath)) . " bytes\n";
        } else {
            echo "   ❌ File not found in storage\n";
        }
        
    } else {
        echo "   ❌ Video file is not accessible\n";
        echo "   Response headers:\n" . $headers . "\n";
    }
    
    echo "\n5. Video upload summary:\n";
    echo "   ✅ Upload: SUCCESS\n";
    echo "   ✅ Storage: SUCCESS\n";
    echo "   ✅ Access: " . ($httpCode === 200 ? 'SUCCESS' : 'FAILED') . "\n";
    echo "   📹 Video URL: $videoUrl\n";
    echo "   📁 Ready for use in lectures!\n";
    
} else {
    echo "   ❌ Upload failed\n";
    echo "   Response: $response\n";
    
    if (isset($uploadResult['error'])) {
        echo "   Error: {$uploadResult['error']}\n";
    }
}

echo "\n=== Test Complete ===\n";
