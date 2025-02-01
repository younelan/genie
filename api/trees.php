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
                $this->handleGet();
                break;
            case 'POST':
                $this->createTree();
                break;
            case 'PUT':
                $this->updateTree();
                break;
            case 'DELETE':
                $this->deleteTree();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function handleGet() {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'list':
                $this->listTrees();
                break;
            case 'details':
                $this->getTreeDetails($_GET['id']);
                break;
            default:
                $this->listTrees(); // Default to list for backward compatibility
                break;
        }
    }

    private function listTrees() {
        $trees = $this->treeModel->getAllTreesByOwner($this->userId);
        foreach ($trees as &$tree) {
            $tree['member_count'] = $this->treeModel->getPersonCount($tree['id']);
        }
        echo json_encode([
            'success' => true,
            'data' => $trees
        ]);
    }

    private function getTree($id) {
        $tree = $this->treeModel->getTree($id);
        if (!$tree) {
            http_response_code(404);
            echo json_encode(['error' => 'Tree not found']);
            return;
        }
        
        // Only allow access if user owns the tree or tree is public
        if ($tree['owner_id'] !== $this->userId && !$tree['is_public']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $tree
        ]);
    }

    private function getTreeDetails($id) {
        try {
            $tree = $this->treeModel->getAllTreesByOwner($this->userId);
            $tree = array_filter($tree, function($t) use ($id) {
                return $t['id'] == $id;
            });
            
            if (empty($tree)) {
                throw new Exception('Tree not found');
            }

            $tree = reset($tree); // Get first (and only) tree
            $tree['member_count'] = $this->treeModel->getPersonCount($tree['id']);

            echo json_encode([
                'success' => true,
                'data' => $tree
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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

    private function updateTree() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Tree ID required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        try {
            // Update tree logic here - you'll need to add this method to TreeModel
            $success = $this->treeModel->updateTree($id, $this->userId, [
                'name' => $data['name'],
                'description' => $data['description'],
                'is_public' => $data['is_public']
            ]);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to update tree');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
