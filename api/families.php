<?php
require_once '../init.php';

class FamiliesAPI {
    private $familyModel;
    private $userId;

    public function __construct($config) {
        $user = new UserModel($config);
        $this->userId = $user->getCurrentUserId();
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->familyModel = new FamilyModel($config);
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
            case 'DELETE':
                $this->handleDelete();
                break;
        }
    }

    private function handleGet() {
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'list':
                $this->getFamiliesByMember($_GET['member_id']);
                break;
            case 'spouses':
                $this->getSpouseFamilies($_GET['member_id']);
                break;
            case 'children':
                $this->getChildFamilies($_GET['member_id']);
                break;
        }
    }

    private function handlePost() {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $this->createFamily($data);
                break;
            case 'addChild':
                $this->addChildToFamily($data);
                break;
            case 'addSpouse':
                $this->addSpouseToFamily($data);
                break;
            case 'updateFamily':
                $this->updateFamily($data);
                break;
        }
    }

    private function handleDelete() {
        $familyId = $_GET['id'] ?? null;
        if ($familyId) {
            $this->deleteFamily($familyId);
        }
    }

    // Implementation of specific methods...
}

$api = new FamiliesAPI($config);
$api->handleRequest();
