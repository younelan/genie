<?php
require_once '../init.php';

class TagsAPI {
    private $tagModel;
    private $userId;

    public function __construct($config) {
        $user = new UserModel($config);
        $this->userId = $user->getCurrentUserId();
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->tagModel = new TagModel($config);
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getTags();
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $action = $data['action'] ?? '';
                
                switch ($action) {
                    case 'add_tag':
                        $this->addTag($data);
                        break;
                    case 'delete_tag':
                        $this->deleteTag($data);
                        break;
                    default:
                        $this->sendError('Invalid action');
                }
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function getTags() {
        $rowId = $_GET['row_id'] ?? null;  // Changed from member_id
        $tagType = $_GET['tag_type'] ?? 'INDI';
        
        if (!$rowId) {
            $this->sendError('Row ID required');
            return;
        }

        try {
            $tags = $this->tagModel->getTagString($rowId, $tagType);
            $this->sendResponse(['tags' => $tags]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function addTag($data) {
        try {
            error_log("Raw tag data: " . print_r($data, true));
            
            if (empty($data['tag']) || empty($data['row_id']) || empty($data['tree_id']) || empty($data['tag_type'])) {
                throw new Exception('All fields are required: tag, row_id, tree_id, tag_type');
            }

            $result = $this->tagModel->addTag([
                'tag' => trim($data['tag']),
                'row_id' => $data['row_id'],    // Changed from member_id
                'tree_id' => $data['tree_id'],
                'tag_type' => $data['tag_type']
            ]);

            if (!$result) {
                throw new Exception('Database error while adding tag');
            }

            $tags = $this->tagModel->getTagString($data['row_id'], $data['tag_type']);
            $this->sendResponse(['tags' => $tags]);
        } catch (Exception $e) {
            error_log("Error adding tag: " . $e->getMessage());
            $this->sendError($e->getMessage());
        }
    }

    private function deleteTag($data) {
        try {
            // All fields are required
            if (empty($data['tag']) || empty($data['member_id']) || empty($data['tree_id']) || empty($data['tag_type'])) {
                throw new Exception('All fields are required: tag, member_id, tree_id, tag_type');
            }

            $result = $this->tagModel->deleteTag([
                'tag' => trim($data['tag']),
                'member_id' => $data['member_id'],
                'tree_id' => $data['tree_id'],
                'tag_type' => $data['tag_type']
            ]);

            if (!$result) {
                throw new Exception('Failed to delete tag');
            }

            $tags = $this->tagModel->getTagString($data['member_id'], $data['tag_type']);
            $this->sendResponse(['tags' => $tags]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function sendResponse($data) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}

// Initialize and handle the API request
$api = new TagsAPI($config);
$api->handleRequest();
