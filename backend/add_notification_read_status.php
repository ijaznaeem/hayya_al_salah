<?php
/**
 * Migration to add read/unread status and user tracking to notifications table
 * Run this script to add the necessary columns for tracking read status
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "Adding read status tracking to notifications table...\n";

    // Check if columns already exist
    $checkColumns = "
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = '$dbname' 
    AND TABLE_NAME = 'notifications' 
    AND COLUMN_NAME IN ('is_read', 'read_at', 'user_id')
    ";
    
    $stmt = $pdo->query($checkColumns);
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Add is_read column if it doesn't exist
    if (!in_array('is_read', $existingColumns)) {
        $sql = "ALTER TABLE `notifications` ADD COLUMN `is_read` BOOLEAN DEFAULT FALSE";
        $pdo->exec($sql);
        echo "✓ Added is_read column\n";
    } else {
        echo "✓ is_read column already exists\n";
    }

    // Add read_at column if it doesn't exist
    if (!in_array('read_at', $existingColumns)) {
        $sql = "ALTER TABLE `notifications` ADD COLUMN `read_at` TIMESTAMP NULL DEFAULT NULL";
        $pdo->exec($sql);
        echo "✓ Added read_at column\n";
    } else {
        echo "✓ read_at column already exists\n";
    }

    // Add user_id column if it doesn't exist (for future user-specific notifications)
    if (!in_array('user_id', $existingColumns)) {
        $sql = "ALTER TABLE `notifications` ADD COLUMN `user_id` VARCHAR(36) NULL DEFAULT NULL";
        $pdo->exec($sql);
        echo "✓ Added user_id column\n";
    } else {
        echo "✓ user_id column already exists\n";
    }

    // Add indexes for better performance
    echo "Adding indexes...\n";
    
    try {
        $pdo->exec("CREATE INDEX idx_is_read ON notifications (is_read)");
        echo "✓ Added index for is_read\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Index for is_read already exists\n";
        } else {
            throw $e;
        }
    }

    try {
        $pdo->exec("CREATE INDEX idx_user_id ON notifications (user_id)");
        echo "✓ Added index for user_id\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Index for user_id already exists\n";
        } else {
            throw $e;
        }
    }

    try {
        $pdo->exec("CREATE INDEX idx_user_read ON notifications (user_id, is_read)");
        echo "✓ Added composite index for user_id and is_read\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Composite index for user_id and is_read already exists\n";
        } else {
            throw $e;
        }
    }

    echo "\n✅ Migration completed successfully!\n";
    echo "The notifications table now supports read/unread status tracking.\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
