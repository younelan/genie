<?php
$dbHost = $config['db']['host'] ?? 'localhost';
$dbName = $config['db']['name'] ?? 'genealogy';
$dbUser = $config['db']['user'] ?? 'root';
$dbPass = $config['db']['pass']??'';

try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
