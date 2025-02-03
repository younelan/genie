<?php
require_once '../init.php';

class TreeAPI {
    private $treeModel;
    private $userId;

    public function __construct($config) {
        $this->userId = $config['current-user'];
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->treeModel = new TreeModel($config);
    }

    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }

    private function sendResponse($data) {
        echo json_encode($data);
        exit;
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $action = $_GET['action'] ?? '';

            switch ($action) {
                case 'list':
                    if ($method !== 'GET') $this->sendError('Method not allowed', 405);
                    $this->listTrees();
                    break;

                case 'get_families':
                    if ($method !== 'GET') $this->sendError('Method not allowed', 405);
                    $this->getFamilies();
                    break;

                case 'details':
                    if ($method !== 'GET') $this->sendError('Method not allowed', 405);
                    $this->getTreeDetails();
                    break;

                case 'create':
                    if ($method !== 'POST') $this->sendError('Method not allowed', 405);
                    $this->createTree();
                    break;

                case 'update':
                    if ($method !== 'PUT') $this->sendError('Method not allowed', 405);
                    $this->updateTree();
                    break;

                case 'delete':
                    if ($method !== 'DELETE') $this->sendError('Method not allowed', 405);
                    $this->deleteTree();
                    break;

                case '':
                    if ($method === 'GET') {
                        $this->getTrees(); // Default action for GET
                    } else {
                        $this->sendError('Action required', 400);
                    }
                    break;

                default:
                    $this->sendError('Invalid action', 400);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function getTrees() {
        $trees = $this->treeModel->getAllTreesByOwner($this->userId);
        foreach ($trees as &$tree) {
            $tree['member_count'] = $this->treeModel->getPersonCount($tree['id']);
        }
        $this->sendResponse([
            'success' => true,
            'data' => $trees
        ]);
    }

    public function getFamilies()
    {
        $familyTreeId = $_GET['tree_id']; // Get tree_id from the request
        $treeData = $this->treeModel->getFamilies($familyTreeId);
        header('Content-Type: application/json');
        echo json_encode($treeData);
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

    private function getTreeDetails() {
        $treeId = $_GET['id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
            return;
        }

        try {
            $trees = $this->treeModel->getAllTreesByOwner($this->userId);
            $tree = array_filter($trees, function($t) use ($treeId) {
                return $t['id'] == $treeId;
            });
            
            if (empty($tree)) {
                $this->sendError('Tree not found', 404);
                return;
            }

            $tree = reset($tree); // Get first (and only) tree
            $tree['member_count'] = $this->treeModel->getPersonCount($tree['id']);

            $this->sendResponse([
                'success' => true,
                'data' => $tree
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
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
        $treeId = $_GET['id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $this->sendError('Invalid request data');
            return;
        }

        try {
            $success = $this->treeModel->updateTree($treeId, $data, $this->userId);
            if ($success) {
                $this->sendResponse(['success' => true]);
            } else {
                $this->sendError('Failed to update tree');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
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

$api = new TreeAPI($config);
$api->handleRequest();
