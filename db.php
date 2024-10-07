<?php
$dbHost = $config['db']['host'] ?? 'localhost';
$dbName = $config['db']['name'] ?? 'genealogy';
$dbUser = $config['db']['user'] ?? 'root';
$dbPass = $config['db']['pass']??'';

try {
    $connection = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $config['connection'] = $connection;
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
