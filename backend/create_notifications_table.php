<?php
/**
 * Migration to create notifications table
 * Run this script to create the notifications table in your database
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

    echo "Creating notifications table...\n";

    $sql = "
    CREATE TABLE IF NOT EXISTS `notifications` (
      `id` varchar(36) NOT NULL,
      `title` varchar(255) NOT NULL,
      `message` text NOT NULL,
      `timestamp` datetime NOT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_timestamp` (`timestamp`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✓ Notifications table created successfully!\n";

    // Insert some sample notifications
    echo "Inserting sample notifications...\n";

    $sampleNotifications = [
        [
            'id' => '550e8400-e29b-41d4-a716-446655440001',
            'title' => 'Welcome to Hayya Al Salah!',
            'message' => 'Thank you for downloading our app. Start your journey of learning prayer with our comprehensive lessons.',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'id' => '550e8400-e29b-41d4-a716-446655440002',
            'title' => 'New Lesson Available',
            'message' => 'A new lesson on prayer times has been added to the app. Check it out now!',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'id' => '550e8400-e29b-41d4-a716-446655440003',
            'title' => 'Prayer Reminder',
            'message' => 'Don\'t forget to perform your daily prayers. Set up prayer time notifications for regular reminders.',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-6 hours'))
        ],
        [
            'id' => '550e8400-e29b-41d4-a716-446655440004',
            'title' => 'App Update Available',
            'message' => 'A new version of the app is available with improved features and bug fixes.',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'id' => '550e8400-e29b-41d4-a716-446655440005',
            'title' => 'Weekend Bonus Content',
            'message' => 'Special weekend content has been unlocked! Explore additional lessons and resources.',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
        ]
    ];

    $insertSql = "INSERT INTO notifications (id, title, message, timestamp) VALUES (?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE title = VALUES(title), message = VALUES(message), timestamp = VALUES(timestamp)";
    
    $stmt = $pdo->prepare($insertSql);
    
    foreach ($sampleNotifications as $notification) {
        $stmt->execute([
            $notification['id'],
            $notification['title'],
            $notification['message'],
            $notification['timestamp']
        ]);
    }

    echo "✓ Sample notifications inserted successfully!\n";
    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
