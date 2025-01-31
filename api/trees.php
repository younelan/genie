<?php
require_once '../init.php';

class TreeAPI {
    private $treeModel;
    private $userId;

    public function __construct($config) {
        $user = new UserModel($config);
        $this->userId = $user->getCurrentUserId();
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->treeModel = new TreeModel($config);
    }

    public function handleRequest() {
        header('Content-Type: application/json');

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                // Use the exact same method as TreeController
                $trees = $this->treeModel->getAllTreesByOwner($this->userId);
                foreach ($trees as &$tree) {
                    $tree['member_count'] = $this->treeModel->getPersonCount($tree['id']);
                }
                echo json_encode([
                    'success' => true,
                    'data' => $trees
                ]);
                break;
                
            case 'POST':
                $this->createTree();
                break;
                
            case 'DELETE':
                $this->deleteTree();
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function createTree() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name is required']);
            return;
        }
        
        $treeId = $this->treeModel->addTree(
            $this->userId,
            $data['name'],
            $data['description'] ?? '',
            $data['is_public'] ?? 0
        );
        
        if (!$treeId) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create tree']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $treeId,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'is_public' => $data['is_public'] ?? 0,
                'owner_id' => $this->userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'member_count' => 0
            ]
        ]);
    }

    private function deleteTree() {
        $treeId = $_GET['id'] ?? null;
        
        if (!$treeId) {
            http_response_code(400);
            echo json_encode(['error' => 'Tree ID required']);
            return;
        }
        
        $success = $this->treeModel->deleteTree($treeId, $this->userId);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete tree']);
        }
    }
}

// Initialize and handle the API request
$api = new TreeAPI($config);
$api->handleRequest();
