<?php

require_once 'config.php';

session_start();

$action = isset($_GET['action']) ? $_GET['action'] : null;

// Establish database connection
try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

switch ($action) {
    case 'login':
    case 'register':
    case 'logout':
        $_SESSION['user_id'] = 1;
        $controller = new UserController($pdo);
        $controller->handleRequest($action);
        break;
    case 'dashboard':
        if (!$isLoggedIn) {
            header("Location: index.php?action=login");
            exit();
        }
        $controller = new DashboardController($pdo);
        $controller->handleRequest();
        break;
    case 'trees':
    case 'add_tree':
        if (!$isLoggedIn) {
            header("Location: index.php?action=login");
            exit();
        }
        $controller = new TreeController($pdo);
        $controller->handleRequest();
        break;
    default:
        // Redirect to login by default if not logged in
        if (!$isLoggedIn) {
            header("Location: index.php?action=login");
            exit();
        }
        // Redirect to dashboard if action is unknown
        header("Location: index.php?action=dashboard");
        exit();
}
