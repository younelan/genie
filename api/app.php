<?php
require_once '../init.php';

class AppAPI {
    private $config;
    private $memberModel;
    private $treeModel;
    private $userId;

    public function __construct($config) {
        $this->config = $config;
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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->handleGet();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => 'Method not allowed'
            ]);
            exit;
        }
    }

    private function handleGet() {
        header('Content-Type: application/json');
        
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'relationship_types':
                // Return the raw config relationship types object
                echo json_encode([
                    'success' => true,
                    'types' => $this->config['relationship_types']  // This is already an object with code => {description} structure
                ]);
                break;

            case 'translations':
                $lang = $_GET['lang'] ?? 'fr';
                if (!isset($this->config['translations'][$lang])) {
                    $lang = 'fr';
                }
                echo json_encode([
                    'success' => true,
                    'translations' => $this->config['translations'][$lang],
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
        exit;
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
        exit;
    }
}

// Initialize and handle the API request
$api = new AppAPI($config);
$api->handleRequest();
