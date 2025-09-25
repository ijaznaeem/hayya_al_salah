<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();

echo "Testing admin login credentials...\n";

try {
    $admin = $db->fetch("SELECT username, email, password FROM users WHERE role = 'admin'");
    
    if ($admin) {
        echo "Admin user found:\n";
        echo "Username: {$admin['username']}\n";
        echo "Email: {$admin['email']}\n";
        
        // Test common passwords
        $testPasswords = ['admin123', 'password', 'admin', '123456'];
        
        foreach ($testPasswords as $password) {
            if (password_verify($password, $admin['password'])) {
                echo "✓ Password: $password\n";
                break;
            }
        }
        
    } else {
        echo "No admin user found.\n";
        
        // Create one
        echo "Creating admin user...\n";
        $adminData = [
            'username' => 'admin',
            'email' => 'admin@hayyaalsalah.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $adminId = $db->insert('users', $adminData);
        echo "✓ Admin user created with password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
