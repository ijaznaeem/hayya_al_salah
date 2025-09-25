<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Controllers/VideoDurationController.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Controllers\VideoDurationController;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing Video Duration Controller...\n\n";

try {
    $controller = new VideoDurationController();
    
    // Test status endpoint
    echo "Testing status endpoint...\n";
    ob_start();
    $controller->status();
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if ($data && $data['success']) {
        echo "✓ Status endpoint working\n";
        echo "  Total videos: " . $data['data']['total_videos'] . "\n";
        echo "  Needs processing: " . $data['data']['needs_processing'] . "\n";
        echo "  With duration: " . $data['data']['with_duration'] . "\n";
        echo "  FFmpeg available: " . ($data['data']['ffmpeg_available'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "✗ Status endpoint failed\n";
        echo "Response: " . $output . "\n";
    }
    
    echo "\nController test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
