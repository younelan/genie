class TagsAPI {
    private $memberModel;
    private $userId;

    public function handleRequest() {
        header('Content-Type: application/json');
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getTags();
                break;
            case 'POST':
                $this->addTag();
                break;
            case 'DELETE':
                $this->deleteTag();
                break;
        }
    }

    private function getTags() {
        $memberId = $_GET['member_id'] ?? null;
        if (!$memberId) {
            http_response_code(400);
            echo json_encode(['error' => 'Member ID required']);
            return;
        }

        $tags = $this->memberModel->getTags($memberId);
        echo json_encode(['success' => true, 'data' => $tags]);
    }

    private function addTag() {
        $data = json_decode(file_get_contents('php://input'), true);
        // ... implementation
    }

    private function deleteTag() {
        $data = json_decode(file_get_contents('php://input'), true);
        // ... implementation
    }
}
