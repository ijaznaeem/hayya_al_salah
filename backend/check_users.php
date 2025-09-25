<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();

echo "Checking for existing users...\n\n";

try {
    $users = $db->fetchAll("SELECT id, username, email, role, created_at FROM users");
    
    if (empty($users)) {
        echo "No users found in database.\n";
        echo "Creating default admin user...\n\n";
        
        // Create default admin user
        $adminData = [
            'username' => 'admin',
            'email' => 'admin@hayyaalsalah.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $adminId = $db->insert('users', $adminData);
        echo "✓ Default admin user created with ID: $adminId\n";
        echo "  Email: admin@hayyaalsalah.com\n";
        echo "  Password: admin123\n\n";
        
    } else {
        echo "Found " . count($users) . " user(s):\n\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "Username: {$user['username']}\n";
            echo "Email: {$user['email']}\n";
            echo "Role: {$user['role']}\n";
            echo "Created: {$user['created_at']}\n";
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
