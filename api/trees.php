<?php
require_once '../init.php';

class TreeAPI {
    private $treeModel;
    private $gedcomModel;  // Add GedcomModel property
    private $userId;

    public function __construct($config) {
        $this->userId = $config['current-user'];
        if (!$this->userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $this->treeModel = new TreeModel($config);
        $this->gedcomModel = new GedcomModel($config);
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
                    if (!isset($_GET['tree_id'])) {
                        $this->sendError('tree_id parameter is required', 400);
                    }
                    $treeId = intval($_GET['tree_id']);
                    
                    // Verify user has access to this tree
                    $trees = $this->treeModel->getAllTreesByOwner($this->userId);
                    $hasAccess = false;
                    foreach ($trees as $tree) {
                        if ($tree['id'] == $treeId) {
                            $hasAccess = true;
                            break;
                        }
                    }
                    
                    if (!$hasAccess) {
                        $this->sendError('Access denied', 403);
                    }
                    
                    try {
                        $result = $this->treeModel->getFamilies($treeId);
                        // Debug log should use the correct data structure
                        error_log("API response data: " . json_encode([
                            'individuals' => count($result['data']['individuals']),
                            'families' => count($result['data']['families']),
                            'children' => count($result['data']['children'])
                        ]));
                        $this->sendResponse($result); // Already contains success and data keys
                    } catch (Exception $e) {
                        $this->sendError($e->getMessage(), 500);
                    }
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

                case 'empty':
                    if ($method !== 'POST') $this->sendError('Method not allowed', 405);
                    $this->emptyTree();
                    break;

                case 'export_gedcom':
                    if ($method !== 'GET') $this->sendError('Method not allowed', 405);
                    $this->exportGedcom();
                    break;

                case 'import_gedcom':
                    if ($method !== 'POST') $this->sendError('Method not allowed', 405);
                    $this->importGedcom();
                    break;

                case 'get_synonyms':
                    if ($method !== 'GET') $this->sendError('Method not allowed', 405);
                    $this->getSynonyms();
                    break;

                case 'add_synonym':
                    if ($method !== 'POST') $this->sendError('Method not allowed', 405);
                    $this->addSynonym();
                    break;

                case 'update_synonym':
                    if ($method !== 'PUT') $this->sendError('Method not allowed', 405);
                    $this->updateSynonym();
                    break;

                case 'delete_synonym':
                    if ($method !== 'DELETE') $this->sendError('Method not allowed', 405);
                    $this->deleteSynonym();
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
            $this->sendError($e->getMessage(), 500);
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
            $this->sendError('Tree ID required', 400);
            return;
        }

        try {
            // Let the model handle all the logic
            $success = $this->treeModel->deleteTreeWithData($treeId, $this->userId);
            
            if (!$success) {
                throw new Exception('Failed to delete tree');
            }
            
            $this->sendResponse(['success' => true]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }

    private function emptyTree() {
        $treeId = $_GET['id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
            return;
        }

        try {
            $success = $this->treeModel->emptyTree($treeId, $this->userId);
            if ($success) {
                $this->sendResponse(['success' => true]);
            } else {
                $this->sendError('Failed to empty tree');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function exportGedcom() {
        $treeId = $_GET['tree_id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
        }

        // Verify user has access to this tree
        $trees = $this->treeModel->getAllTreesByOwner($this->userId);
        $tree = null;
        foreach ($trees as $t) {
            if ($t['id'] == $treeId) {
                $tree = $t;
                break;
            }
        }
        
        if (!$tree) {
            $this->sendError('Access denied', 403);
        }

        try {
            $gedcom = $this->gedcomModel->exportGedcom($treeId);
            
            // Set headers for file download
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . 
                   preg_replace('/[^a-zA-Z0-9_-]/', '', $tree['name']) . 
                   '_export.ged"');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            echo $gedcom;
            exit;

        } catch (Exception $e) {
            $this->sendError('Failed to export GEDCOM: ' . $e->getMessage(), 500);
        }
    }

    private function importGedcom() {
        if (empty($_FILES['file']) || $_FILES['file']['error']) {
            $this->sendError('No file uploaded or upload error');
        }

        if (empty($_POST['name'])) {
            $this->sendError('Tree name is required');
        }

        try {
            // Create new tree
            $treeId = $this->treeModel->addTree(
                $this->userId,
                $_POST['name'],
                'Imported from GEDCOM file'
            );

            if (!$treeId) {
                throw new Exception('Failed to create tree');
            }

            // Import GEDCOM data
            $result = $this->gedcomModel->import($_FILES['file']['tmp_name'], [
                'tree_id' => $treeId
            ]);

            $this->sendResponse([
                'success' => true,
                'data' => [
                    'tree_id' => $treeId,
                    'stats' => $result
                ]
            ]);

        } catch (Exception $e) {
            // If import fails, try to delete the tree
            if (isset($treeId)) {
                $this->treeModel->deleteTreeWithData($treeId, $this->userId);
            }
            $this->sendError($e->getMessage());
        }
    }

    private function getSynonyms() {
        $treeId = $_GET['tree_id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
        }

        try {
            $synonyms = $this->treeModel->getAllSynonyms($treeId);
            $this->sendResponse([
                'success' => true,
                'data' => $synonyms
            ]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function addSynonym() {
        $treeId = $_GET['tree_id'] ?? null;
        if (!$treeId) {
            $this->sendError('Tree ID is required');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['key']) || !isset($data['value'])) {
            $this->sendError('Key and value are required');
        }

        try {
            $success = $this->treeModel->addSynonym($treeId, $data['key'], $data['value']);
            if ($success) {
                $this->sendResponse(['success' => true]);
            } else {
                $this->sendError('Failed to add synonym');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function updateSynonym() {
        $synonymId = $_GET['id'] ?? null;
        $treeId = $_GET['tree_id'] ?? null;
        if (!$synonymId || !$treeId) {
            $this->sendError('Synonym ID and Tree ID are required');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['key']) || !isset($data['value'])) {
            $this->sendError('Key and value are required');
        }

        try {
            $success = $this->treeModel->updateSynonym($synonymId, $treeId, $data['key'], $data['value']);
            if ($success) {
                $this->sendResponse(['success' => true]);
            } else {
                $this->sendError('Failed to update synonym');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function deleteSynonym() {
        $synonymId = $_GET['id'] ?? null;
        $treeId = $_GET['tree_id'] ?? null;
        if (!$synonymId || !$treeId) {
            $this->sendError('Synonym ID and Tree ID are required');
        }

        try {
            $success = $this->treeModel->deleteSynonym($synonymId, $treeId);
            if ($success) {
                $this->sendResponse(['success' => true]);
            } else {
                $this->sendError('Failed to delete synonym');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}

$api = new TreeAPI($config);
$api->handleRequest();
