<?php

require_once 'Model/User.php';

class UserController {
    private $userModel;

    public function __construct(PDO $pdo) {
        $this->userModel = new User($pdo);
    }

    public function handleRequest($action) {
        switch ($action) {
            case 'login':
                $this->handleLogin();
                break;
            case 'register':
                $this->handleRegistration();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            default:
                // Redirect to login by default if action is unknown
                $this->redirectTo('login');
        }
    }

    private function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            if ($this->userModel->login($email, $password)) {
                $this->redirectTo('dashboard');
            } else {
                $this->renderTemplate('login.html', ['error' => $this->userModel->getError()]);
            }
        } else {
            $this->renderTemplate('login.html');
        }
    }

    private function handleRegistration() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            if ($this->userModel->register($name, $email, $password)) {
                $this->redirectTo('dashboard');
            } else {
                $this->renderTemplate('register.html', ['error' => $this->userModel->getError()]);
            }
        } else {
            $this->renderTemplate('register.html');
        }
    }

    private function handleLogout() {
        $this->userModel->logout();
        $this->redirectTo('login');
    }

    private function redirectTo($action) {
        header("Location: index.php?action=$action");
        exit();
    }

    private function renderTemplate($template, $data = []) {
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
