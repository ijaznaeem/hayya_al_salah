<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Connect to database
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking users table structure...\n";
    
    // Describe users table
    $stmt = $pdo->query("DESCRIBE users");
    while ($column = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
