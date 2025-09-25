<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Debug Large Video Upload ===\n";

// Get fresh admin token
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
curl_close($ch);

$authData = json_decode($response, true);
$token = $authData['token'];

echo "Got fresh token: " . substr($token, 0, 20) . "...\n";

// Create a temp copy of the video to test MIME type
$videoFile = 'C:\Users\naeem\Downloads\converted.mp4';
echo "Original file MIME type: " . mime_content_type($videoFile) . "\n";
echo "File size: " . filesize($videoFile) . " bytes\n";

// Test with shorter timeout but with progress
echo "Testing upload with progress...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/upload/video',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($videoFile, 'video/mp4', 'converted.mp4')
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => false,
    CURLOPT_NOPROGRESS => false,
    CURLOPT_PROGRESSFUNCTION => function($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) {
        if ($uploadSize > 0) {
            $progress = round(($uploaded / $uploadSize) * 100, 2);
            echo "\rUploading: $progress% ($uploaded / $uploadSize bytes)";
        }
    },
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token
    ],
    CURLOPT_TIMEOUT => 120 // 2 minutes timeout
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\nHTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($error) {
    echo "cURL Error: $error\n";
}

?>
