<?php
require_once '../init.php';

class IndividualsAPI {
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
            case 'PUT':
                $this->handlePut();
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
                $this->listMembers();
                break;
            case 'search':
                $this->searchMembers();
                break;
            case 'stats':
                $this->getStats();
                break;
            case 'details':
                $this->getMemberDetails();
                break;
            case 'tags':
                $this->getTags();
                break;
            case 'get_descendants':
                $this->getDescendants();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    }

    private function handlePost() {
        $data = json_decode(file_get_contents('php://input'), true);
        switch ($data['action'] ?? '') {
            case 'create':
                $this->createMember($data);
                break;
            case 'add_tag':
                $this->addTag($data);
                break;
            case 'delete_tag':
                $this->deleteTag($data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    }

    private function handlePut() {
        $data = json_decode(file_get_contents('php://input'), true);
        $this->updateMember($data);
    }

    private function handleDelete() {
        $memberId = $_GET['id'] ?? null;
        if ($memberId) {
            $this->deleteMember($memberId);
        }
    }

    private function listMembers() {
        $treeId = $_GET['tree_id'] ?? null;
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 70;
        $offset = ($page - 1) * $limit;

        if (!$treeId) {
            http_response_code(400);
            echo json_encode(['error' => 'Tree ID required']);
            return;
        }

        $members = $this->treeModel->getMembersByTreeId($treeId, $offset, $limit);
        $totalMembers = $this->treeModel->getPersonCount($treeId);
        $lastUpdates = $this->treeModel->getMembersByTreeId($treeId, 0, 40, 'updated_at DESC');

        echo json_encode([
            'success' => true,
            'data' => [
                'members' => $members,
                'lastUpdates' => $lastUpdates,
                'totalMembers' => $totalMembers,
                'totalPages' => ceil($totalMembers / $limit),
                'currentPage' => $page
            ]
        ]);
    }

    private function getStats() {
        $treeId = $_GET['tree_id'] ?? null;
        
        if (!$treeId) {
            http_response_code(400);
            echo json_encode(['error' => 'Tree ID required']);
            return;
        }

        $totalMembers = $this->treeModel->getPersonCount($treeId);
        $countByGender = $this->treeModel->countMembersByTreeId($treeId);
        $synonyms = $this->treeModel->getSynonymsByTreeId($treeId);
        $countByLastname = $this->treeModel->countTreeMembersByField($treeId, 'last_name', $synonyms);
        $countByFirstname = $this->treeModel->countTreeMembersByField($treeId, 'first_name', $synonyms);
        $totalRelationships = $this->treeModel->countRelationshipsByTreeId($treeId);

        echo json_encode([
            'success' => true,
            'data' => [
                'byGender' => array_merge($countByGender, ['Total' => $totalMembers]),
                'relations' => ['Total' => $totalRelationships],
                'byFirstName' => $countByFirstname,
                'byLastName' => $countByLastname
            ]
        ]);
    }

    // Additional methods for other actions
    private function searchMembers() {
        $treeId = $_GET['tree_id'] ?? null;
        $query = $_GET['query'] ?? '';

        if (!$treeId) {
            http_response_code(400);
            echo json_encode(['error' => 'Tree ID required']);
            return;
        }

        $results = $this->treeModel->searchMembers($treeId, $query);
        echo json_encode(['success' => true, 'data' => $results]);
    }

    private function getMemberDetails() {
        $memberId = $_GET['id'] ?? null;
        
        try {
            $member = $this->memberModel->getMemberById($memberId);
            if (!$member) {
                throw new Exception('Member not found');
            }

            // Get member's tags as a comma-separated string
            $tags = $this->memberModel->getTagString($memberId);
            $member['tags'] = $tags;

            // Get additional data
            $spouseFamilies = $this->memberModel->getSpouseFamilies($memberId);
            $childFamilies = $this->memberModel->getChildFamilies($memberId);

            echo json_encode([
                'success' => true,
                'data' => [
                    'member' => $member,
                    'spouseFamilies' => $spouseFamilies,
                    'childFamilies' => $childFamilies
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to fetch member details',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getTags() {
        $memberId = $_GET['member_id'] ?? null;
        
        if (!$memberId) {
            http_response_code(400);
            echo json_encode(['error' => 'Member ID required']);
            return;
        }

        try {
            $tags = $this->memberModel->getTagString($memberId);
            echo json_encode([
                'success' => true,
                'data' => ['tags' => $tags]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function createMember($data) {
        try {
            $newMemberId = $this->memberModel->addMember($data);
            if ($newMemberId) {
                echo json_encode([
                    'success' => true,
                    'data' => ['id' => $newMemberId]
                ]);
            } else {
                throw new Exception('Failed to create member');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to create member',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function updateMember($data) {
        try {
            $memberId = $data['id'];
            $updateData = [
                'memberId' => $memberId,
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'],
                'dateOfBirth' => $data['birth_date'],
                'placeOfBirth' => $data['birth_place'],
                'dateOfDeath' => $data['death_date'],
                'placeOfDeath' => $data['death_place'],
                'gender' => $data['gender'],
                'alive' => $data['alive'],
                'source' => $data['source'] ?? ''
            ];

            $success = $this->memberModel->updateMember($updateData);
            
            if ($success) {
                // Return updated member data
                $member = $this->memberModel->getMemberById($memberId);
                echo json_encode([
                    'success' => true,
                    'data' => $member
                ]);
            } else {
                throw new Exception('Failed to update member');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function deleteMember($memberId) {
        try {
            $success = $this->memberModel->deleteMember($memberId);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete member');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Failed to delete member',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function addTag($data) {
        try {
            if (!isset($data['tag']) || !isset($data['member_id']) || !isset($data['tree_id'])) {
                throw new Exception('Missing required tag data');
            }

            $newTag = [
                'tag' => $data['tag'],
                'member_id' => $data['member_id'],
                'tree_id' => $data['tree_id']
            ];

            $result = $this->memberModel->addTag($newTag);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => ['id' => $result]
                ]);
            } else {
                throw new Exception('Failed to add tag');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function deleteTag($data) {
        try {
            if (!isset($data['tag']) || !isset($data['member_id']) || !isset($data['tree_id'])) {
                throw new Exception('Missing required tag data');
            }

            $tagToDelete = [
                'tag' => $data['tag'],
                'member_id' => $data['member_id'],
                'tree_id' => $data['tree_id']
            ];

            $result = $this->memberModel->deleteTag($tagToDelete);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete tag');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getDescendants() {
        $memberId = $_GET['member_id'] ?? null;
        if (!$memberId) {
            http_response_code(400);
            echo json_encode(['error' => 'Member ID required']);
            return;
        }

        try {
            $data = $this->buildDescendantsTree($memberId);
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function buildDescendantsTree($memberId, $visitedMembers = []) {
        // Prevent infinite loops
        if (in_array($memberId, $visitedMembers)) {
            return null;
        }
        $visitedMembers[] = $memberId;

        $member = $this->memberModel->getMemberById($memberId);
        if (!$member) return null;

        $data = [
            'id' => $memberId,
            'name' => $member['first_name'] . ' ' . $member['last_name'],
            'data' => [
                'gender' => $member['gender'],
                'birth_date' => $member['birth_date']
            ],
            'marriages' => []
        ];

        // Get spouse families
        $spouseFamilies = $this->memberModel->getSpouseFamilies($memberId);
        foreach ($spouseFamilies as $family) {
            $marriageData = [
                'id' => $family['id'],
                'spouse' => null,
                'children' => []
            ];

            // Add spouse data if exists
            if ($family['spouse_id']) {
                $spouse = $this->memberModel->getMemberById($family['spouse_id']);
                if ($spouse) {
                    $marriageData['spouse'] = [
                        'id' => $spouse['id'],
                        'name' => $spouse['first_name'] . ' ' . $spouse['last_name'],
                        'data' => ['gender' => $spouse['gender']]
                    ];
                }
            }

            // Get and process children
            if (isset($family['children'])) {
                foreach ($family['children'] as $child) {
                    $childTree = $this->buildDescendantsTree($child['id'], $visitedMembers);
                    if ($childTree) {
                        $marriageData['children'][] = $childTree;
                    }
                }
            }

            $data['marriages'][] = $marriageData;
        }

        return $data;
    }
}

// Initialize and handle the API request
$api = new IndividualsAPI($config);
$api->handleRequest();
