<?php
/**
 * Test script to verify absolute URL processing for lectures and topics
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use HayyaAlSalah\Controllers\LectureController;
use HayyaAlSalah\Controllers\TopicController;

echo "=== Testing Absolute URL Processing ===\n\n";

try {
    // Test Lecture Controller
    echo "1. Testing Lecture Controller:\n";
    echo "   - Simulating API call to get all lectures...\n";
    
    // Capture output for lecture controller
    ob_start();
    $lectureController = new LectureController();
    $lectureController->index();
    $lectureOutput = ob_get_clean();
    
    $lectureData = json_decode($lectureOutput, true);
    
    if ($lectureData && isset($lectureData['success']) && $lectureData['success']) {
        echo "   ✓ Lectures fetched successfully\n";
        
        if (!empty($lectureData['data']['data'])) {
            $firstLecture = $lectureData['data']['data'][0];
            echo "   - First lecture URLs:\n";
            echo "     * Video URL: " . ($firstLecture['video_url'] ?? 'N/A') . "\n";
            echo "     * Thumbnail URL: " . ($firstLecture['thumbnail_url'] ?? 'N/A') . "\n";
            echo "     * Reference URL: " . ($firstLecture['reference_file_url'] ?? 'N/A') . "\n";
            
            // Check if URLs are absolute
            $videoUrl = $firstLecture['video_url'] ?? '';
            $isAbsolute = preg_match('/^https?:\/\//', $videoUrl);
            echo "     * URLs are " . ($isAbsolute ? "ABSOLUTE ✓" : "relative ✗") . "\n";
        }
    } else {
        echo "   ✗ Failed to fetch lectures\n";
        echo "   Error: " . ($lectureData['error'] ?? 'Unknown error') . "\n";
    }
    
    echo "\n2. Testing Topic Controller:\n";
    echo "   - Simulating API call to get all topics...\n";
    
    // Capture output for topic controller
    ob_start();
    $topicController = new TopicController();
    $topicController->index();
    $topicOutput = ob_get_clean();
    
    $topicData = json_decode($topicOutput, true);
    
    if ($topicData && isset($topicData['success']) && $topicData['success']) {
        echo "   ✓ Topics fetched successfully\n";
        
        if (!empty($topicData['data'])) {
            $firstTopic = $topicData['data'][0];
            echo "   - First topic URLs:\n";
            echo "     * Image URL: " . ($firstTopic['image_url'] ?? 'N/A') . "\n";
            
            // Check if URLs are absolute
            $imageUrl = $firstTopic['image_url'] ?? '';
            if (!empty($imageUrl)) {
                $isAbsolute = preg_match('/^https?:\/\//', $imageUrl);
                echo "     * URLs are " . ($isAbsolute ? "ABSOLUTE ✓" : "relative ✗") . "\n";
            } else {
                echo "     * No image URL found\n";
            }
        }
    } else {
        echo "   ✗ Failed to fetch topics\n";
        echo "   Error: " . ($topicData['error'] ?? 'Unknown error') . "\n";
    }

    echo "\n=== URL Processing Configuration ===\n";
    echo "Base URL (APP_URL): " . ($_ENV['APP_URL'] ?? 'Not set') . "\n";
    
} catch (Exception $e) {
    echo "Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
