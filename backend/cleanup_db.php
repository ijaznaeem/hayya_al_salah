<?php

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use HayyaAlSalah\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();

echo "Cleaning up duplicate data...\n";

// Delete duplicate topics (keep only the first 4)
$db->query("DELETE FROM topics WHERE id > 4");
echo "Removed duplicate topics\n";

// Clean up any orphaned lectures
$db->query("DELETE FROM lectures WHERE topic_id > 4");
echo "Removed orphaned lectures\n";

echo "Database cleanup completed!\n";
