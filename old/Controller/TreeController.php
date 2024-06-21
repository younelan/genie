<?php

class TreeController {
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
            case 'trees':
                $this->handleTrees();
                break;
            case 'add_tree':
                $this->handleAddTree();
                break;
            // Add more cases for other tree-related actions as needed
            default:
                $this->handleTrees();
        }
    }

    private function handleTrees() {
        // Example data for trees page
        $data = []; 

        $this->renderTemplate('trees.html', $data);
    }

    private function handleAddTree() {
        // Handle logic for adding a tree

        // After adding, redirect to trees page
        $this->redirectTo('trees');
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


