<?php
/**
 * Debug script to check notifications table and data
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

    echo "🔍 Checking notifications table status...\n\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "✅ Table 'notifications' exists\n";

        // Check table structure
        echo "\n📋 Table structure:\n";
        $stmt = $pdo->query("DESCRIBE notifications");
        while ($row = $stmt->fetch()) {
            echo "  {$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
        }

        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifications");
        $count = $stmt->fetch()['count'];
        echo "\n📊 Total records: {$count}\n";

        if ($count > 0) {
            // Show sample records
            echo "\n📝 Sample records:\n";
            $stmt = $pdo->query("SELECT id, title, timestamp FROM notifications ORDER BY timestamp DESC LIMIT 5");
            while ($row = $stmt->fetch()) {
                echo "  ID: {$row['id']}\n";
                echo "  Title: {$row['title']}\n";
                echo "  Timestamp: {$row['timestamp']}\n";
                echo "  ---\n";
            }
        }

        // Test recent notifications query
        echo "\n🔍 Testing recent notifications query...\n";
        try {
            $stmt = $pdo->prepare("SELECT id, title, message, timestamp, created_at, updated_at 
                                  FROM notifications 
                                  WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? DAY)
                                  ORDER BY timestamp DESC");
            $stmt->execute([30]);
            $recentCount = $stmt->rowCount();
            echo "✅ Recent notifications query works: {$recentCount} records found\n";
        } catch (Exception $e) {
            echo "❌ Recent notifications query failed: " . $e->getMessage() . "\n";
        }

    } else {
        echo "❌ Table 'notifications' does not exist\n";
        echo "Please run: php create_notifications_table.php\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
