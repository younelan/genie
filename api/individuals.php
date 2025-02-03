<?php
require_once '../init.php';
function logapache($foo) {
    if(is_array($foo)) {
        $foo = print_r($foo,true);
    }
    $foo .= "\n";
    file_put_contents('php://stderr', print_r($foo, TRUE)) ;
}  

logapache($_POST);

class IndividualsAPI {
    private $memberModel;
    private $familyModel;
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
            case 'autocomplete_member':
                $term = $_GET['term'] ?? '';
                $memberId = $_GET['member_id'] ?? null;
                $treeId = $_GET['tree_id'] ?? null;
                $results = $this->memberModel->autocompleteMember($term, $memberId, $treeId);
                echo json_encode($results);
                break;
            case 'get_relationship_types':
                $treeId = $_GET['tree_id'] ?? 1;
                $types = $this->memberModel->getRelationshipTypes($treeId);
                echo json_encode($types);
                break;
            case 'get_relationships':
                $memberId = $_GET['member_id'] ?? null;
                if ($memberId) {
                    $relationships = $this->memberModel->getMemberRelationships($memberId);
                    echo json_encode(['success' => true, 'relationships' => $relationships]);
                }
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    }

    private function handlePost() {
        try {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'add_relationship':
                    $type = $_POST['type'] ?? '';
                    switch ($type) {
                        case 'spouse':
                            $this->addSpouse($_POST);
                            break;
                        case 'child':
                            $this->addChild($_POST);
                            break;
                        case 'parent':
                            $this->addParent($_POST);
                            break;
                        case 'other':
                            $this->addOther($_POST);
                            break;
                        default:
                            throw new Exception('Invalid relationship type');
                    }
                    break;
                // ...other cases...
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
            error_log('Creating member with data: ' . print_r($data, true));
            
            if (!isset($data['tree_id'])) {
                throw new Exception('Tree ID is required');
            }

            // Validate required fields
            $requiredFields = ['first_name', 'gender', 'tree_id'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }

            $memberData = [
                'treeId' => $data['tree_id'],
                'firstName' => $data['first_name'],
                'lastName' => $data['last_name'] ?? '',
                'dateOfBirth' => $data['birth_date'] ?? null,
                'gender' => $data['gender'],
                'placeOfBirth' => null,
                'dateOfDeath' => null,
                'alive' => isset($data['alive']) ? $data['alive'] : '1'
            ];

            error_log('Processed member data: ' . print_r($memberData, true));

            $newMemberId = $this->memberModel->addMember($memberData);
            if ($newMemberId) {
                echo json_encode([
                    'success' => true,
                    'data' => ['id' => $newMemberId]
                ]);
            } else {
                throw new Exception('Failed to create member');
            }
        } catch (Exception $e) {
            error_log('Error creating member: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
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

    private function addRelationship($data) {
        try {
            switch ($data['relationship_type'] ?? '') {
                case 'spouse':
                    $response = $this->addSpouse($data);
                    break;
                case 'child':
                    $response = $this->addChild($data);
                    break;
                case 'parent':
                    $response = $this->addParent($data);
                    break;
                case 'other':
                    $response = $this->addOther($data);
                    break;
                default:
                    throw new Exception('Unsupported relationship type');
            }
            echo json_encode([
                'success' => true,
                'message' => 'Relationship added',
                'data' => $response
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function addSpouse($data) {
        $alive = $data['alive'] ?? 1;
        $marriageDate = $data['marriage_date'] ?? null;
        $memberId = $data['member_id'];
        $treeId = $data['tree_id'];

        // If spouse is totally new
        if (($data['spouse_type'] ?? '') === 'new') {
            $newSpouseData = [
                'firstName' => $data['spouse_first_name'] ?? null,
                'lastName' => $data['spouse_last_name'] ?? null,
                'treeId' => $treeId,
                'gender' => $data['spouse_gender'] ?? null,
                'dateOfBirth' => $data['spouse_birth_date'] ?? null,
                'alive' => $alive,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $spouseId = $this->memberModel->addMember($newSpouseData);
            $spouseGender = $newSpouseData['gender'];
        } else {
            $spouseId = $data['spouse_id'];
            $spouse = $this->memberModel->getMemberById($spouseId);
            $spouseGender = $spouse['gender'] ?? 'M';
        }

        // Assign husband/wife
        if ($spouseGender === 'F') {
            $husband = $memberId;
            $wife = $spouseId;
        } else {
            $husband = $spouseId;
            $wife = $memberId;
        }

        // Handle empty family creation
        if (isset($data['create_empty']) && $data['create_empty']) {
            $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $data['member_gender'] === 'M' ? $memberId : null,
                'wife_id' => $data['member_gender'] === 'F' ? $memberId : null,
                'marriage_date' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $familyId = $this->treeModel->createFamily($familyData);
            return ['familyId' => $familyId];
        }

        // Create family record
        $familyData = [
            'tree_id' => $treeId,
            'husband_id' => $husband,
            'wife_id' => $wife,
            'marriage_date' => $marriageDate,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $familyId = $this->familyModel->createFamily($familyData);

        return ['familyId' => $familyId, 'spouseId' => $spouseId];
    }

    private function addChild($data) {
        if (!isset($data['child_type'])) {
            throw new Exception('Missing child type');
        }

        // Create new child if needed
        if ($data['child_type'] === 'new') {
            $childData = [
                'firstName' => $data['child_first_name'] ?? null,
                'lastName' => $data['child_last_name'] ?? null,
                'treeId' => $data['tree_id'],
                'gender' => $data['child_gender'] ?? 'M',
                'dateOfBirth' => $data['child_birth_date'] ?? null,
                'alive' => 1
            ];
            $childId = $this->memberModel->addMember($childData);
        } else {
            $childId = $data['child_id'] ?? null;
            if (!$childId) throw new Exception('Child ID required for existing child');
        }

        // Handle family assignment
        $familyId = $data['family_id'] ?? 'new';
        if ($familyId === 'new') {
            $familyData = [
                'tree_id' => $data['tree_id'],
                'husband_id' => $data['member_gender'] === 'M' ? $data['member_id'] : null,
                'wife_id' => $data['member_gender'] === 'F' ? $data['member_id'] : null,
                'marriage_date' => null
            ];
            $familyId = $this->familyModel->createFamily($familyData);
        }

        // Link child to family
        $success = $this->familyModel->addChildToFamily($familyId, $childId, $data['tree_id']);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'child_id' => $childId,
                    'family_id' => $familyId
                ]
            ]);
        } else {
            throw new Exception('Failed to add child to family');
        }
    }

    private function addParent($data) {
        $alive1 = $data['parent1_alive'] ?? 1;
        $alive2 = $data['parent2_alive'] ?? 1;
        $childId = $data['member_id'];
        $treeId = $data['tree_id'];

        // Create or fetch parent1
        if (($data['parent1_type'] ?? '') === 'new') {
            $parent1Data = [
                'firstName' => $data['parent1_first_name'] ?? null,
                'lastName' => $data['parent1_last_name'] ?? null,
                'treeId' => $treeId,
                'birth_date' => $data['parent1_birth_date'] ?? null,
                'gender' => $data['parent1_gender'] ?? null,
                'alive' => $alive1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $parent1Id = $this->memberModel->addMember($parent1Data);
            $parent1Gender = $parent1Data['gender'];
        } else {
            $parent1Id = $data['parent1_id'] ?? null;
            $parent1Gender = null;
        }

        // Create or fetch parent2
        if (($data['second_parent_option'] ?? '') === 'new') {
            $parent2Data = [
                'firstName' => $data['parent2_first_name'] ?? null,
                'lastName' => $data['parent2_last_name'] ?? null,
                'treeId' => $treeId,
                'birth_date' => $data['parent2_birth_date'] ?? null,
                'gender' => $data['parent2_gender'] ?? null,
                'alive' => $alive2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $parent2Id = $this->memberModel->addMember($parent2Data);
            $parent2Gender = $parent2Data['gender'];
        } else {
            $parent2Id = $data['parent2_id'] ?? null;
            $parent2Gender = null;
        }

        // Assign husband/wife
        $husband = $parent1Id;
        $wife = $parent2Id;
        // You can refine logic based on $parent1Gender/$parent2Gender if needed.

        $familyData = [
            'tree_id' => $treeId,
            'husband_id' => $husband,
            'wife_id' => $wife,
            'marriage_date' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $familyId = $this->familyModel->createFamily($familyData);
        $this->familyModel->addChildToFamily($familyId, $childId, $treeId);

        return ['parents' => [$parent1Id, $parent2Id], 'child' => $childId, 'familyId' => $familyId];
    }

    private function addOther($data) {
        // For non-parent/spouse relationships
        // Example: store in person_relationships with type = $data['other_type'] or similar
        $firstId = $data['member_id'];
        $secondId = $data['other_id'] ?? null; // or create new if needed
        // ... e.g. $this->memberModel->addOtherRelationship($firstId, $secondId, $data['relationship_type']);
        return ['msg' => 'Other relationship linked'];
    }

    private function swapRelationship($data) {
        // Implementation for swapping relationships
    }

    private function updateRelationship($data) {
        // Implementation for updating relationships
    }
}

// Initialize and handle the API request
$api = new IndividualsAPI($config);
$api->handleRequest();
