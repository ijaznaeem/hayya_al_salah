<?php
/**
 * Test script for notification unread count and read status endpoints
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$baseUrl = 'https://salah.imtiazkausar.org.pk/api';

// Test endpoints
$endpoints = [
    'GET /notifications' => $baseUrl . '/notifications',
    'GET /notifications/unread/count' => $baseUrl . '/notifications/unread/count',
    'GET /notifications/stats' => $baseUrl . '/notifications/stats',
];

echo "🧪 Testing Notification API Endpoints\n";
echo "=====================================\n\n";

foreach ($endpoints as $name => $url) {
    echo "Testing: $name\n";
    echo "URL: $url\n";
    
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'header' => [
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            echo "❌ Failed to fetch data\n";
        } else {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "✅ Success:\n";
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo "⚠️ Invalid JSON response:\n";
                echo $response . "\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Test creating a sample notification
echo "Testing: POST /notifications (Creating sample notification)\n";
$createUrl = $baseUrl . '/notifications';
$sampleNotification = [
    'title' => 'Test Unread Notification',
    'message' => 'This is a test notification to check unread count functionality.',
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'content' => json_encode($sampleNotification),
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($createUrl, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to create notification\n";
    } else {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ Notification created successfully:\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            
            // Test marking as read if we got the ID
            if (isset($data['data']['id'])) {
                $notificationId = $data['data']['id'];
                echo "\nTesting: PUT /notifications/$notificationId/read (Mark as read)\n";
                
                $readContext = stream_context_create([
                    'http' => [
                        'method' => 'PUT',
                        'header' => [
                            'Content-Type: application/json',
                            'Accept: application/json'
                        ],
                        'timeout' => 10
                    ]
                ]);
                
                $readResponse = file_get_contents($baseUrl . "/notifications/$notificationId/read", false, $readContext);
                
                if ($readResponse === false) {
                    echo "❌ Failed to mark as read\n";
                } else {
                    $readData = json_decode($readResponse, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "✅ Marked as read successfully:\n";
                        echo json_encode($readData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                    } else {
                        echo "⚠️ Invalid JSON response:\n";
                        echo $readResponse . "\n";
                    }
                }
            }
        } else {
            echo "⚠️ Invalid JSON response:\n";
            echo $response . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 Test completed!\n";
