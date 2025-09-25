<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Database.php';

use Dotenv\Dotenv;
use HayyaAlSalah\Database;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = Database::getInstance();
$result = $db->fetchAll('DESCRIBE lectures');
print_r($result);
