<?php
// Simple test to see if the uploads path is being matched
$testPath = '/uploads/images/68c7ca06814f6_1757923846.png';

echo "Testing path: $testPath\n";
echo "Pattern match: ";

if (preg_match('#^/uploads/(.+)$#', $testPath, $matches)) {
    echo "MATCH\n";
    echo "Captured: {$matches[1]}\n";
} else {
    echo "NO MATCH\n";
}

// Test actual request
echo "\nActual request testing:\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set') . "\n";

// Simulate what happens in index.php
$requestUri = $_SERVER['REQUEST_URI'] ?? $testPath;
$requestPath = parse_url($requestUri, PHP_URL_PATH);
echo "Parsed path: $requestPath\n";

if (preg_match('#^/uploads/(.+)$#', $requestPath, $matches)) {
    echo "✓ Pattern matches!\n";
    $relativePath = $matches[1];
    $filePath = __DIR__ . '/../storage/uploads/' . $relativePath;
    echo "File path would be: $filePath\n";
    echo "File exists: " . (file_exists($filePath) ? 'YES' : 'NO') . "\n";
} else {
    echo "✗ Pattern doesn't match\n";
}
