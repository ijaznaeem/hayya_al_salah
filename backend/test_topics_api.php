<?php
// Test script for topics API endpoints

// Set the base URL for the API
$baseUrl = 'http://localhost:8000/api';

function testApiEndpoint($url, $description) {
    echo "\n=== Testing: $description ===\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "cURL Error: $error\n";
        return false;
    }
    
    echo "HTTP Status: $httpCode\n";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Raw Response: $response\n";
        }
    } else {
        echo "No response received\n";
    }
    
    return $httpCode >= 200 && $httpCode < 300;
}

echo "Starting Topics API Tests...\n";

// Test getting all topics
testApiEndpoint("$baseUrl/topics", "Get All Topics");

// Test getting a specific topic (if exists)
testApiEndpoint("$baseUrl/topics/1", "Get Topic by ID (1)");

echo "\nTests completed.\n";
?>
