<?php
require_once 'config.php';
require_once 'Controller/UserController.php';
require_once 'Controller/DashboardController.php';
require_once 'Controller/TreeController.php';


// Establish database connection
try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// session_start();

$action = isset($_GET['action']) ? $_GET['action'] : null;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);


require "newindex.php";