<?php
/**
 * Test script for notifications API
 * Run this to test if the notifications endpoints are working
 */

// Base URL for your API
$baseUrl = 'https://salah.imtiazkausar.org.pk/api';
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

echo "🧪 Testing Notifications API Endpoints\n";
echo "=====================================\n\n";

// Test 1: Get all notifications
echo "1️⃣  Testing GET /notifications\n";
echo "URL: {$baseUrl}/notifications\n";
$result = testEndpoint("{$baseUrl}/notifications");
echo "Status Code: {$result['http_code']}\n";
if ($result['error']) {
    echo "❌ Error: {$result['error']}\n";
} else {
    echo "✅ Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// Test 2: Get recent notifications
echo "2️⃣  Testing GET /notifications/recent\n";
echo "URL: {$baseUrl}/notifications/recent\n";
$result = testEndpoint("{$baseUrl}/notifications/recent");
echo "Status Code: {$result['http_code']}\n";
if ($result['error']) {
    echo "❌ Error: {$result['error']}\n";
} else {
    echo "✅ Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// Test 3: Get notification stats
echo "3️⃣  Testing GET /notifications/stats\n";
echo "URL: {$baseUrl}/notifications/stats\n";
$result = testEndpoint("{$baseUrl}/notifications/stats");
echo "Status Code: {$result['http_code']}\n";
if ($result['error']) {
    echo "❌ Error: {$result['error']}\n";
} else {
    echo "✅ Response: " . substr($result['response'], 0, 200) . "...\n";
}
echo "\n";

// Test 4: Create a new notification (POST)
echo "4️⃣  Testing POST /notifications\n";
echo "URL: {$baseUrl}/notifications\n";
$newNotification = [
    'title' => 'Test Notification',
    'message' => 'This is a test notification created via API',
    'timestamp' => date('Y-m-d H:i:s')
];
$result = testEndpoint("{$baseUrl}/notifications", 'POST', $newNotification);
echo "Status Code: {$result['http_code']}\n";
if ($result['error']) {
    echo "❌ Error: {$result['error']}\n";
} else {
    echo "✅ Response: " . substr($result['response'], 0, 200) . "...\n";
    
    // Try to parse the response to get the created notification ID
    $responseData = json_decode($result['response'], true);
    if ($responseData && isset($responseData['data']['id'])) {
        $notificationId = $responseData['data']['id'];
        echo "📝 Created notification ID: {$notificationId}\n";
        
        // Test 5: Get specific notification
        echo "\n5️⃣  Testing GET /notifications/{$notificationId}\n";
        echo "URL: {$baseUrl}/notifications/{$notificationId}\n";
        $result = testEndpoint("{$baseUrl}/notifications/{$notificationId}");
        echo "Status Code: {$result['http_code']}\n";
        if ($result['error']) {
            echo "❌ Error: {$result['error']}\n";
        } else {
            echo "✅ Response: " . substr($result['response'], 0, 200) . "...\n";
        }
    }
}
echo "\n";

echo "🎉 API Testing Complete!\n";
echo "\nNext steps:\n";
echo "1. Run the migration script: php create_notifications_table.php\n";
echo "2. Update your Flutter app to use the new API endpoint\n";
echo "3. Test the notifications in your mobile app\n";
?>
