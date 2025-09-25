<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();

echo "Users table structure:\n";
$columns = $db->fetchAll('DESCRIBE users');
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}

echo "\nUsers data:\n";
$users = $db->fetchAll('SELECT * FROM users LIMIT 5');
foreach ($users as $user) {
    echo "ID: {$user['id']}, Username: " . ($user['username'] ?? 'N/A') . ", Email: " . ($user['email'] ?? 'N/A') . "\n";
}

echo "\nTesting admin password...\n";
$admin = $db->fetch("SELECT username, email, password_hash FROM users WHERE role = 'admin'");

if ($admin) {
    $testPasswords = ['admin123', 'password', 'admin', '123456'];
    
    echo "Testing passwords for {$admin['email']}:\n";
    $passwordFound = false;
    
    foreach ($testPasswords as $password) {
        if (password_verify($password, $admin['password_hash'])) {
            echo "✓ Correct password: $password\n";
            $passwordFound = true;
            break;
        }
    }
    
    if (!$passwordFound) {
        echo "❌ None of the test passwords worked.\n";
        echo "Updating password to 'admin123'...\n";
        
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->query('UPDATE users SET password_hash = ? WHERE id = ?', [$newHash, $admin['id']]);
        echo "✓ Password updated to 'admin123'\n";
    }
}
