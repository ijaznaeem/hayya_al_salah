<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing upload functionality...\n";

// Test 1: Check if uploads directory exists
$uploadDir = __DIR__ . '/storage/uploads';
echo "Upload directory: $uploadDir\n";
echo "Upload directory exists: " . (is_dir($uploadDir) ? 'YES' : 'NO') . "\n";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    echo "Created upload directory\n";
}

// Test 2: Check if video subdirectories exist
$videoDir = $uploadDir . '/videos';
$imageDir = $uploadDir . '/images';
$documentDir = $uploadDir . '/documents';

foreach ([$videoDir, $imageDir, $documentDir] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    }
}

// Test 3: Create a test file
$testFile = sys_get_temp_dir() . '/test_upload.txt';
file_put_contents($testFile, 'This is a test file for upload');
echo "Test file created: $testFile\n";

// Test 4: Simulate file upload
$_FILES['file'] = [
    'name' => 'test_upload.txt',
    'type' => 'text/plain',
    'tmp_name' => $testFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($testFile)
];

echo "Simulated \$_FILES:\n";
print_r($_FILES);

// Test 5: Check PHP upload settings
echo "\nPHP Upload Settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";

?>
