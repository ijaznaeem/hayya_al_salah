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
    
    echo "Database connected successfully!\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "Users table does not exist. Creating...\n";
        
        $createTable = "
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTable);
        echo "Users table created!\n";
    } else {
        echo "Users table exists.\n";
    }
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute(['admin@example.com']);
    
    if ($stmt->rowCount() == 0) {
        echo "Admin user does not exist. Creating...\n";
        
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')";
        $stmt = $pdo->prepare($insertAdmin);
        $stmt->execute(['admin', 'admin@example.com', $hashedPassword]);
        
        echo "Admin user created!\n";
        echo "Email: admin@example.com\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "Admin user already exists.\n";
    }
    
    // List all users
    echo "\nAll users:\n";
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users");
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
