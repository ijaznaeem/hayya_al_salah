<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();
$result = $db->fetch("SELECT title, duration FROM lectures WHERE title = 'Test Video - Big Buck Bunny'");
echo "Title: " . $result['title'] . "\n";
echo "Duration: " . $result['duration'] . "\n";
