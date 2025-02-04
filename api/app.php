<?php
require_once '../init.php';

class AppAPI {
    private $memberModel;
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
        $this->memberModel = new MemberModel($config);
        $this->treeModel = new TreeModel($config);
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    private function handleGet() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'relationship_types':
                $treeId = $_GET['tree_id'] ?? 1;
                $types = $this->treeModel->getRelationshipTypes($treeId);
                echo json_encode([
                    'success' => true,
                    'types' => $types
                ]);
                break;
                
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid action'
                ]);
                break;
        }
    }

    private function handlePost() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        $action = $data['action'] ?? '';

        switch ($action) {
            case 'swap_relationship':
                $relationshipId = $data['relationship_id'] ?? null;
                if (!$relationshipId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Relationship ID required']);
                    return;
                }
                try {
                    $success = $this->memberModel->swapRelationship($relationshipId);
                    echo json_encode([
                        'success' => $success,
                        'message' => $success ? 'Relationship swapped successfully' : 'Failed to swap relationship'
                    ]);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid action'
                ]);
                break;
        }
    }
}

// Initialize and handle the API request
$api = new AppAPI($config);
$api->handleRequest();
