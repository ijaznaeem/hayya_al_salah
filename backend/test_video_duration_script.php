<?php
/**
 * Test script for video duration analyzer
 * This script creates a test entry and verifies the video duration script works
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$db = Database::getInstance();

echo "=== Video Duration Analyzer Test ===\n";
echo "This script tests the video duration analyzer functionality.\n\n";

// Insert a test lecture with a remote video URL (if one doesn't exist)
$testVideoUrl = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
$testTitle = 'Test Video - Big Buck Bunny';

// Check if test lecture already exists
$existing = $db->fetch(
    "SELECT id FROM lectures WHERE title = :title", 
    ['title' => $testTitle]
);

if (!$existing) {
    echo "Creating test lecture entry...\n";
    $lectureId = $db->insert('lectures', [
        'title' => $testTitle,
        'description' => 'Test video for duration analysis',
        'video_url' => $testVideoUrl,
        'topic_id' => 1,
        'lesson_number' => 999,
        'duration' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    echo "Test lecture created with ID: $lectureId\n\n";
} else {
    echo "Test lecture already exists with ID: {$existing['id']}\n\n";
}

echo "Now you can run the video duration analyzer:\n";
echo "php analyze_video_durations.php --dry-run --limit=1\n";
echo "php analyze_video_durations.php --limit=1  (to actually update)\n\n";

echo "To clean up the test entry later:\n";
echo "DELETE FROM lectures WHERE title = '$testTitle';\n";
