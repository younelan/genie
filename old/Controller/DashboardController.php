<?php

class DashboardController {
    private $userModel;

    public function __construct(PDO $pdo) {
        $this->userModel = new User($pdo);
    }

    public function handleRequest() {
        if (!$this->userModel->isLoggedIn()) {
            $this->redirectTo('login');
        }

        $action = isset($_GET['action']) ? $_GET['action'] : null;

        switch ($action) {
            case 'dashboard':
                $this->handleDashboard();
                break;
            // Add more cases for other dashboard actions as needed
            default:
                $this->handleDashboard();
        }
    }

    private function handleDashboard() {
        // Example data for dashboard
        $data = []; 

        $this->renderTemplate('dashboard.html', $data);
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


