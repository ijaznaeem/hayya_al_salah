<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

try {
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    // Connect to MySQL server (without specifying database)
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "Connected to MySQL server successfully.\n";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$dbname}' created or already exists.\n";

    // Connect to the specific database
    $pdo->exec("USE `{$dbname}`");

    // Create tables
    $sql = "
        -- Create topics table
        CREATE TABLE IF NOT EXISTS topics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_url VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Create lectures table
        CREATE TABLE IF NOT EXISTS lectures (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            video_url VARCHAR(500) NOT NULL,
            thumbnail_url VARCHAR(500),
            duration VARCHAR(20) NOT NULL,
            lesson_number INT NOT NULL,
            reference_file_url VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
            INDEX idx_topic_id (topic_id),
            INDEX idx_lesson_number (lesson_number),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Create users table
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_username (username),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        -- Create user_favorites table
        CREATE TABLE IF NOT EXISTS user_favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            lecture_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_lecture (user_id, lecture_id),
            INDEX idx_user_id (user_id),
            INDEX idx_lecture_id (lecture_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Split and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "Tables created successfully.\n";

    // Insert sample data
    insertSampleData($pdo);

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

function insertSampleData($pdo) {
    echo "Inserting sample data...\n";

    // Insert sample topics
    $topics = [
        [
            'title' => 'Islamic Prayer (Salah)',
            'description' => 'Learn the fundamentals of Islamic prayer, including proper postures, recitations, and spiritual significance.',
            'image_url' => 'https://example.com/images/prayer.jpg'
        ],
        [
            'title' => 'Quran Reading',
            'description' => 'Master the art of Quranic recitation with proper Tajweed and understanding.',
            'image_url' => 'https://example.com/images/quran.jpg'
        ],
        [
            'title' => 'Islamic History',
            'description' => 'Explore the rich history of Islam and the lives of important Islamic figures.',
            'image_url' => 'https://example.com/images/history.jpg'
        ],
        [
            'title' => 'Arabic Language',
            'description' => 'Learn Arabic language basics to better understand Islamic texts.',
            'image_url' => 'https://example.com/images/arabic.jpg'
        ]
    ];

    foreach ($topics as $topic) {
        $stmt = $pdo->prepare("
            INSERT INTO topics (title, description, image_url) 
            VALUES (:title, :description, :image_url)
        ");
        $stmt->execute($topic);
    }

    // Insert sample lectures
    $lectures = [
        // Prayer Topic (ID: 1)
        [
            'topic_id' => 1,
            'title' => 'Introduction to Prayer',
            'description' => 'Understanding the importance and significance of prayer in Islam',
            'video_url' => 'https://example.com/videos/prayer-intro.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/prayer-intro.jpg',
            'duration' => '15:30',
            'lesson_number' => 1,
            'reference_file_url' => 'https://example.com/docs/prayer-basics.pdf'
        ],
        [
            'topic_id' => 1,
            'title' => 'Wudu (Ablution)',
            'description' => 'Step-by-step guide to performing ablution before prayer',
            'video_url' => 'https://example.com/videos/wudu.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/wudu.jpg',
            'duration' => '12:45',
            'lesson_number' => 2,
            'reference_file_url' => 'https://example.com/docs/wudu-guide.pdf'
        ],
        [
            'topic_id' => 1,
            'title' => 'Prayer Postures',
            'description' => 'Learn the correct postures and movements in prayer',
            'video_url' => 'https://example.com/videos/prayer-postures.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/prayer-postures.jpg',
            'duration' => '18:20',
            'lesson_number' => 3
        ],

        // Quran Topic (ID: 2)
        [
            'topic_id' => 2,
            'title' => 'Arabic Alphabet',
            'description' => 'Learn the Arabic letters and their pronunciation',
            'video_url' => 'https://example.com/videos/arabic-alphabet.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/arabic-alphabet.jpg',
            'duration' => '20:15',
            'lesson_number' => 1,
            'reference_file_url' => 'https://example.com/docs/arabic-alphabet.pdf'
        ],
        [
            'topic_id' => 2,
            'title' => 'Basic Tajweed Rules',
            'description' => 'Introduction to the rules of Quranic recitation',
            'video_url' => 'https://example.com/videos/tajweed-basics.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/tajweed-basics.jpg',
            'duration' => '25:30',
            'lesson_number' => 2
        ],

        // History Topic (ID: 3)
        [
            'topic_id' => 3,
            'title' => 'Life of Prophet Muhammad (PBUH)',
            'description' => 'Early life and prophethood of Muhammad (PBUH)',
            'video_url' => 'https://example.com/videos/prophet-biography.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/prophet-biography.jpg',
            'duration' => '32:45',
            'lesson_number' => 1,
            'reference_file_url' => 'https://example.com/docs/prophet-biography.pdf'
        ],

        // Arabic Topic (ID: 4)
        [
            'topic_id' => 4,
            'title' => 'Arabic Grammar Basics',
            'description' => 'Introduction to Arabic grammar and sentence structure',
            'video_url' => 'https://example.com/videos/arabic-grammar.mp4',
            'thumbnail_url' => 'https://example.com/thumbs/arabic-grammar.jpg',
            'duration' => '28:10',
            'lesson_number' => 1
        ]
    ];

    foreach ($lectures as $lecture) {
        // Ensure all required fields are present
        $lecture['reference_file_url'] = $lecture['reference_file_url'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO lectures (topic_id, title, description, video_url, thumbnail_url, duration, lesson_number, reference_file_url) 
            VALUES (:topic_id, :title, :description, :video_url, :thumbnail_url, :duration, :lesson_number, :reference_file_url)
        ");
        $stmt->execute($lecture);
    }

    // Create admin user
    $adminUser = [
        'username' => 'admin',
        'email' => 'admin@hayyaalsalah.com',
        'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin'
    ];

    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role) 
        VALUES (:username, :email, :password_hash, :role)
    ");
    $stmt->execute($adminUser);

    echo "Sample data inserted successfully.\n";
    echo "Admin user created - Email: admin@hayyaalsalah.com, Password: admin123\n";
}
