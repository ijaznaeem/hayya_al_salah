<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

echo "Existing admin users:\n";
$stmt = $pdo->query('SELECT id, username, email, role FROM users WHERE role = "admin"');
while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
}

// Try to login with existing admin
echo "\nTesting login with existing admin...\n";
$stmt = $pdo->prepare('SELECT id, username, email, password_hash, role FROM users WHERE username = ? AND role = "admin"');
$stmt->execute(['admin']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Found admin user: {$admin['username']} ({$admin['email']})\n";
    
    // Test password
    if (password_verify('admin123', $admin['password_hash'])) {
        echo "Password 'admin123' is correct!\n";
    } else {
        echo "Password 'admin123' is incorrect. Updating...\n";
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $updateStmt->execute([$newHash, $admin['id']]);
        echo "Password updated to 'admin123'\n";
    }
} else {
    echo "No admin user found with username 'admin'\n";
}

?>
