<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Debug Upload Backend ===\n";

// Debug 1: Check what happens when we hit the upload endpoint directly
echo "1. Testing direct upload endpoint...\n";

// Create a small test file
$testContent = "This is a test video file";
$testFile = sys_get_temp_dir() . '/test_debug.mp4';
file_put_contents($testFile, $testContent);

// Get admin token
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

echo "Login response: $response\n";
echo "Login HTTP code: $httpCode\n";

$authData = json_decode($response, true);
if (!$authData || !isset($authData['token'])) {
    echo "Failed to get auth token from response\n";
    print_r($authData);
    exit(1);
}

$token = $authData['token'];
echo "Got auth token: " . substr($token, 0, 20) . "...\n";

// Test the upload with debug info
echo "2. Testing upload with minimal file...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost:8000/api/upload/video',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'file' => new CURLFile($testFile, 'video/mp4', 'test_debug.mp4')
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
if ($error) {
    echo "cURL Error: $error\n";
}

// Clean up
unlink($testFile);

echo "\n3. Let's check what \$_FILES looks like in a direct PHP simulation...\n";

// Direct PHP file upload simulation
$_POST = [];
$_FILES = [
    'file' => [
        'name' => 'test.mp4',
        'type' => 'video/mp4',
        'tmp_name' => '/tmp/nonexistent', // This will trigger the error
        'error' => UPLOAD_ERR_OK,
        'size' => 1234
    ]
];

echo "Simulated \$_FILES:\n";
print_r($_FILES);

echo "Check: isset(\$_FILES['file']): " . (isset($_FILES['file']) ? 'YES' : 'NO') . "\n";
echo "Check: \$_FILES['file']['error']: " . $_FILES['file']['error'] . "\n";
echo "Check: UPLOAD_ERR_OK constant: " . UPLOAD_ERR_OK . "\n";
echo "Check: condition result: " . (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK ? 'PASS' : 'FAIL') . "\n";

// Let's also check what happens if we manually test the condition from UploadController
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo "Would trigger error: No file uploaded or upload error\n";
    echo "Error code meanings:\n";
    echo "0 (UPLOAD_ERR_OK): No error\n";
    echo "1 (UPLOAD_ERR_INI_SIZE): File too large (php.ini)\n";
    echo "2 (UPLOAD_ERR_FORM_SIZE): File too large (form)\n";
    echo "3 (UPLOAD_ERR_PARTIAL): Partial upload\n";
    echo "4 (UPLOAD_ERR_NO_FILE): No file uploaded\n";
    echo "6 (UPLOAD_ERR_NO_TMP_DIR): No tmp directory\n";
    echo "7 (UPLOAD_ERR_CANT_WRITE): Can't write to disk\n";
    echo "8 (UPLOAD_ERR_EXTENSION): Extension stopped upload\n";
}

?>
