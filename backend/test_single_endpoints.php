<?php
/**
 * Test single lecture and topics endpoints for absolute URL processing
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use HayyaAlSalah\Controllers\LectureController;
use HayyaAlSalah\Controllers\TopicController;

echo "=== Testing Single Endpoints ===\n\n";

try {
    // Test single lecture
    echo "1. Testing Single Lecture (ID: 1):\n";
    
    ob_start();
    $lectureController = new LectureController();
    $lectureController->show(1);
    $lectureOutput = ob_get_clean();
    
    $lectureData = json_decode($lectureOutput, true);
    
    if ($lectureData && isset($lectureData['success']) && $lectureData['success']) {
        echo "   ✓ Single lecture fetched successfully\n";
        $lecture = $lectureData['data'];
        echo "   - Lecture: " . $lecture['title'] . "\n";
        echo "   - Video URL: " . $lecture['video_url'] . "\n";
        echo "   - Thumbnail URL: " . $lecture['thumbnail_url'] . "\n";
        echo "   - Reference URL: " . ($lecture['reference_file_url'] ?? 'N/A') . "\n";
        
        // Check related lessons
        if (isset($lecture['related_lessons']) && !empty($lecture['related_lessons'])) {
            echo "   - Related lessons: " . count($lecture['related_lessons']) . "\n";
            echo "     First related video URL: " . $lecture['related_lessons'][0]['video_url'] . "\n";
        }
    } else {
        echo "   ✗ Failed to fetch single lecture\n";
    }
    
    echo "\n2. Testing Topics:\n";
    
    ob_start();
    $topicController = new TopicController();
    $topicController->index();
    $topicOutput = ob_get_clean();
    
    $topicData = json_decode($topicOutput, true);
    
    if ($topicData && isset($topicData['success']) && $topicData['success']) {
        echo "   ✓ Topics fetched successfully\n";
        echo "   - Total topics: " . count($topicData['data']) . "\n";
        
        if (!empty($topicData['data'])) {
            $firstTopic = $topicData['data'][0];
            echo "   - First topic: " . $firstTopic['title'] . "\n";
            echo "   - Image URL: " . ($firstTopic['image_url'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ✗ Failed to fetch topics\n";
    }

} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
