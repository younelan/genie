<?php

require_once 'User.php';

class Controller {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['login'])) {
                $this->handleLogin($_POST['email'], $_POST['password']);
            } elseif (isset($_POST['register'])) {
                $this->handleRegistration($_POST['name'], $_POST['email'], $_POST['password']);
            } elseif (isset($_POST['logout'])) {
                $this->handleLogout();
            }
        }

        $this->renderTemplate();
    }

    private function handleLogin($email, $password) {
        if ($this->user->login($email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $this->renderTemplate(['error' => $this->user->getError()]);
        }
    }

    private function handleRegistration($name, $email, $password) {
        if ($this->user->register($name, $email, $password)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $this->renderTemplate(['error' => $this->user->getError()]);
        }
    }

    private function handleLogout() {
        $this->user->logout();
        header('Location: index.php');
        exit();
    }

    private function renderTemplate($data = []) {
        $template = 'login.html';

        if ($this->user->isLoggedIn()) {
            $template = 'dashboard.html';
            $data['user_id'] = $_SESSION['user_id'];
        } elseif (isset($_GET['action']) && $_GET['action'] === 'register') {
            $template = 'register.html';
        }

        // Load the template content
        $content = file_get_contents('templates/' . $template);

        // Replace placeholders with data
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        echo $content;
    }
}

?>

