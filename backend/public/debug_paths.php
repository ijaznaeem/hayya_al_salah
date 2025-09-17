<?php
echo "Current directory (__DIR__): " . __DIR__ . "\n";
echo "Parent directory: " . dirname(__DIR__) . "\n";
echo "Storage path: " . __DIR__ . '/../storage/uploads/' . "\n";
echo "Resolved storage path: " . realpath(__DIR__ . '/../storage/uploads/') . "\n";

// Test a specific file
$testFile = 'images/68c7ca06814f6_1757923846.png';
$fullPath = __DIR__ . '/../storage/uploads/' . $testFile;
echo "Test file path: $fullPath\n";
echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

// Alternative path construction
$altPath = dirname(__DIR__) . '/storage/uploads/' . $testFile;
echo "Alternative path: $altPath\n";
echo "Alternative exists: " . (file_exists($altPath) ? 'YES' : 'NO') . "\n";
