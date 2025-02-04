<?php
require_once '../init.php';

function apachelog($foo) {
    if(is_array($foo)) {
        $foo = print_r($foo,true);
    }
    $foo .= "\n";
    file_put_contents('php://stderr', print_r($foo, TRUE));
}

class FamiliesAPI {
    private $familyModel;
    private $memberModel;
    private $userId;
    private $requestData;  // Store request data properly

    public function __construct($config) {
        $user = new UserModel($config);
        $this->userId = $user->getCurrentUserId();
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->familyModel = new FamilyModel($config);
        $this->memberModel = new MemberModel($config);
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        
        // Parse request data once
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requestData = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->requestData = $_POST; // Fallback to POST data if JSON parse fails
            }
        }
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    private function handleGet() {
        $action = $_GET['action'] ?? '';
        $memberId = $_GET['member_id'] ?? null;

        if (!$memberId) {
            echo json_encode(['success' => false, 'message' => 'Member ID required']);
            return;
        }

        try {
            switch ($action) {
                // case 'list':
                //     $families = $this->familyModel->getFamiliesByMember($memberId);
                //     echo json_encode(['success' => true, 'families' => $families]);
                //     break;
                case 'spouses':
                case 'get_spouse_families':
                    $families = $this->memberModel->getSpouseFamilies($memberId);
                    echo json_encode(['success' => true, 'spouse_families' => $families]);
                    break;
                case 'children':
                    $families = $this->memberModel->getChildFamilies($memberId);
                    echo json_encode(['success' => true, 'child_families' => $families]);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                    break;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handlePost() {
        try {
            if (!isset($this->requestData['type'])) {
                throw new Exception('Missing relationship type');
            }

            $result = null;
            switch ($this->requestData['type']) {
                case 'create_family':
                    $result = $this->createFamily($this->requestData);
                    break;
                case 'add_child':
                    $result = $this->addChildToFamily($this->requestData);
                    break;
                case 'remove_child':
                    $result = $this->removeChildFromFamily($this->requestData);
                    break;
                case 'add_spouse_to_family':
                    $result = $this->addSpouseToFamily($this->requestData);
                    break;
                default:
                    throw new Exception('Invalid action type');
            }

            echo json_encode(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function handleDelete() {
        $familyId = $_GET['id'] ?? null;
        if (!$familyId) {
            http_response_code(400);
            echo json_encode(['error' => 'Family ID required']);
            return;
        }

        try {
            $success = $this->familyModel->deleteFamily($familyId);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function createFamily($data) {
        if (!isset($data['tree_id'])) {
            throw new Exception('Tree ID is required');
        }

        $familyData = [
            'tree_id' => $data['tree_id'],
            'husband_id' => $data['husband_id'] ?? null,
            'wife_id' => $data['wife_id'] ?? null,
            'marriage_date' => $data['marriage_date'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->familyModel->createFamily($familyData);
    }

    private function addChildToFamily($data) {
        if (!isset($data['family_id']) || !isset($data['child_id']) || !isset($data['tree_id'])) {
            throw new Exception('Missing required data for adding child to family');
        }

        return $this->familyModel->addChildToFamily(
            $data['family_id'],
            $data['child_id'],
            $data['tree_id']
        );
    }

    private function removeChildFromFamily($data) {
        error_log(print_r($data, true));
        if (!isset($data['family_id']) || !isset($data['child_id'])) {
            throw new Exception('Missing required data for removing child from family');
        }

        // Fix parameter order to match FamilyModel::removeChildFromFamily($childId, $familyId)
        return $this->familyModel->removeChildFromFamily(
            $data['child_id'],    // childId should be first
            $data['family_id']    // familyId should be second
        );
    }

    private function addSpouseToFamily($data) {
        try {
            if (!isset($data['family_id'], $data['spouse_position'], $data['tree_id'], $data['spouse_type'])) {
                throw new Exception('Missing required parameters');
            }

            $result = $this->familyModel->addSpouseToFamily($data);
            return ['message' => 'Spouse added successfully'];
        } catch (Exception $e) {
            error_log("Error adding spouse: " . $e->getMessage());
            throw $e;
        }
    }
}

$api = new FamiliesAPI($config);
$api->handleRequest();
