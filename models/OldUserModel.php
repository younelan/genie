<?php

class User {
    private $db; // PDO database connection
    private $error = '';

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
        session_start();
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        print_r($user);
        print_r(crypt($password));
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        } else {
            $this->error = 'Invalid email or password';
            return false;
        }
    }

    public function register($name, $email, $password) {
        // Check if email exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $this->error = 'Email already exists';
            return false;
        }

        // Insert new user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $success = $stmt->execute([$name, $email, $password_hash]);

        if ($success) {
            $_SESSION['user_id'] = $this->db->lastInsertId();
            return true;
        } else {
            $this->error = 'Registration failed';
            return false;
        }
    }

    public function logout() {
        session_destroy();
        unset($_SESSION['user_id']);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getError() {
        return $this->error;
    }
}

?>
