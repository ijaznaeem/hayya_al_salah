<?php

echo "=== MIME Type Detection Test ===\n\n";

// Create test files and check their MIME types
$testImagePath = __DIR__ . '/test_image.png';
$testDocPath = __DIR__ . '/test_document.txt';

// Create a test image (simple PNG)
$imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAI9jINuGwAAAABJRU5ErkJggg==');
file_put_contents($testImagePath, $imageData);

// Create a test document
file_put_contents($testDocPath, "This is a test document for upload testing.\nCreated at: " . date('Y-m-d H:i:s'));

echo "Test files created:\n";
echo "1. Image file: $testImagePath\n";
echo "   MIME type: " . mime_content_type($testImagePath) . "\n";
echo "   File size: " . filesize($testImagePath) . " bytes\n";

echo "\n2. Document file: $testDocPath\n";
echo "   MIME type: " . mime_content_type($testDocPath) . "\n";
echo "   File size: " . filesize($testDocPath) . " bytes\n";

// Check what MIME types are allowed
echo "\n3. Allowed MIME types in UploadController:\n";
$allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$allowedDocumentTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];

echo "   Images: " . implode(', ', $allowedImageTypes) . "\n";
echo "   Documents: " . implode(', ', $allowedDocumentTypes) . "\n";

// Check if our test files match
$imageMime = mime_content_type($testImagePath);
$docMime = mime_content_type($testDocPath);

echo "\n4. Compatibility check:\n";
echo "   Image compatible: " . (in_array($imageMime, $allowedImageTypes) ? '✓' : '✗') . "\n";
echo "   Document compatible: " . (in_array($docMime, $allowedDocumentTypes) ? '✓' : '✗') . "\n";

// Test with actual file upload simulation
echo "\n5. Simulating upload validation:\n";

function validateFileType($filePath, $allowedTypes, $typeName) {
    if (!file_exists($filePath)) {
        echo "   ✗ File not found: $filePath\n";
        return false;
    }
    
    $mimeType = mime_content_type($filePath);
    echo "   - $typeName file MIME type: $mimeType\n";
    
    if (in_array($mimeType, $allowedTypes)) {
        echo "   ✓ $typeName file type is valid\n";
        return true;
    } else {
        echo "   ✗ $typeName file type is invalid\n";
        echo "     Allowed types: " . implode(', ', $allowedTypes) . "\n";
        return false;
    }
}

validateFileType($testImagePath, $allowedImageTypes, 'Image');
validateFileType($testDocPath, $allowedDocumentTypes, 'Document');

// Cleanup
unlink($testImagePath);
unlink($testDocPath);

echo "\n=== Test Complete ===\n";
