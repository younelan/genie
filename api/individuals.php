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
                if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
                    $this->handlePut();
                } else {
                    $this->handlePost();
                }
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
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $_POST;  // Fallback to POST if JSON parse fails
            }
            
            logapache("POST data received:");
            logapache($data);
            
            $action = $data['action'] ?? $_GET['action'] ?? '';
            
            switch ($action) {
                case 'add_tag':
                    $this->addTag($data);
                    break;
                case 'delete_tag':
                    $this->deleteTag($data);
                    break;
                case 'create': // Added case for creating a member
                    $this->createMember($data);
                    break;
                case 'add_relationship': // New case to handle relationship additions properly
                    $this->addRelationship($data);
                    break;
                case 'edit_relationship':
                    $this->updateRelationship($_GET['id'] ?? null, $data);
                    break;
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function handlePut() {
        // Support both form data and JSON for backward compatibility
        $data = $_POST;
        if (empty($_POST)) {
            $data = json_decode(file_get_contents('php://input'), true);
        }
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

        // Add this line to get the members
        $members = $this->treeModel->getMembersByTreeId($treeId, $offset, $limit);
        $totalMembers = $this->treeModel->getPersonCount($treeId);
        $lastUpdates = $this->treeModel->getMembersByTreeId($treeId, 0, 40, 'updated_at DESC');

        echo json_encode([
            'success' => true,
            'data' => [
                'members' => $members,  // Now this will contain actual member data
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

            // Ensure alive value is consistent
            //$member['alive'] = $member['alive'] === '1' ? '1' : '0';
            logapache("Member alive value: " . $member['alive']);

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

        // Map incoming data to expected format
        $memberData = [
            'firstName'   => $data['first_name'] ?? null,
            'lastName'    => $data['last_name'] ?? null,
            'dateOfBirth' => $data['birth_date'] ?? null,
            'gender'      => $data['gender'] ?? null,
            'alive'       => $data['alive'] ?? 1,
            'treeId'      => $data['tree_id'] ?? null
        ];

        // Corrected the validation to use the proper keys
        if (!$memberData['firstName'] || !$memberData['treeId']) {
            $this->sendError('First name and tree ID are required');
            return;
        }

        try {
            $memberId = $this->memberModel->addMember($memberData);
            if ($memberId) {
                $this->sendResponse([
                    'success' => true,
                    'data' => ['id' => $memberId]
                ]);
            } else {
                $this->sendError('Failed to create member');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function sendResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function sendError($message, $code = 400) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    private function updateMember($data) {
        try {
            logapache("Updating member with data:");
            logapache($data);

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
                'alive' => ($data['alive'] === '1' || $data['alive'] === true) ? '1' : '0',
                'source' => $data['source'] ?? ''
            ];

            logapache("Transformed alive value: " . $updateData['alive']);

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
            logapache("Adding tag with data:");
            logapache($data);

            if (empty($data['tag']) || empty($data['member_id']) || empty($data['tree_id'])) {
                throw new Exception('Missing required tag data');
            }

            $newTag = [
                'tag' => trim($data['tag']),
                'member_id' => $data['member_id'],
                'tree_id' => $data['tree_id']
            ];

            $result = $this->memberModel->addTag($newTag);
            if ($result) {
                // Return updated tag list
                $tags = $this->memberModel->getTagString($data['member_id']);
                echo json_encode([
                    'success' => true,
                    'data' => ['tags' => $tags]
                ]);
            } else {
                throw new Exception('Failed to add tag');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function deleteTag($data) {
        try {
            logapache("Deleting tag with data:");
            logapache($data);

            if (empty($data['tag']) || empty($data['member_id']) || empty($data['tree_id'])) {
                throw new Exception('Missing required tag data');
            }

            $tagToDelete = [
                'tag' => trim($data['tag']),
                'member_id' => $data['member_id'],
                'tree_id' => $data['tree_id']
            ];

            $result = $this->memberModel->deleteTag($tagToDelete);
            if ($result) {
                // Return updated tag list
                $tags = $this->memberModel->getTagString($data['member_id']);
                echo json_encode([
                    'success' => true,
                    'data' => ['tags' => $tags]
                ]);
            } else {
                throw new Exception('Failed to delete tag');
            }
        } catch (Exception $e) {
            http_response_code(400);
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
            logapache("Relationship data received:");
            logapache($data);
            
            // Extract relationship type - either from form data or action type
            $relationType = $data['relationship_type'] ?? $data['type'] ?? '';
            
            switch ($relationType) {
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
                    throw new Exception('Unsupported relationship type: ' . $relationType);
            }

            // Always return a consistent response format
            if (!isset($response['success'])) {
                $response = [
                    'success' => true,
                    'data' => $response
                ];
            }
            
            logapache("Response:");
            logapache($response);
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            http_response_code(400); // Change to 400 for client errors
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function addSpouse($data) {
        $defaultDate = date('Y-m-d H:i:s');
        
        // Validate required base data
        if (empty($data['tree_id']) || empty($data['member_id'])) {
            throw new Exception('Missing required data: tree_id or member_id');
        }

        $treeId = $data['tree_id'];
        $memberId = $data['member_id'];
        $spouseId = null;

        // Handle empty family creation first
        if (isset($data['create_empty']) && $data['create_empty']) {
            $memberInfo = $this->memberModel->getMemberById($memberId);
            if (!$memberInfo) {
                throw new Exception('Member not found');
            }

            $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $memberInfo['gender'] === 'M' ? $memberId : null,
                'wife_id' => $memberInfo['gender'] === 'F' ? $memberId : null,
                'marriage_date' => null,
                'created_at' => $defaultDate,
                'updated_at' => $defaultDate
            ];
            
            $familyId = $this->familyModel->createFamily($familyData);
            if (!$familyId) {
                throw new Exception('Failed to create empty family');
            }
            
            return [
                'success' => true,
                'data' => ['family_id' => $familyId]
            ];
        }

        // Handle spouse based on type
        switch ($data['spouse_type']) {
            case 'existing':
                if (!isset($data['spouse_id'])) {
                    throw new Exception('Spouse ID required for existing spouse');
                }
                $spouseId = $data['spouse_id'];
                $spouse = $this->memberModel->getMemberById($spouseId);
                if (!$spouse) {
                    throw new Exception('Selected spouse not found');
                }
                $spouseGender = $spouse['gender'];
                break;

            case 'new':
                if (empty($data['spouse_first_name']) || empty($data['spouse_last_name'])) {
                    throw new Exception('First name and last name required for new spouse');
                }
                $spouseData = [
                    'firstName' => $data['spouse_first_name'],
                    'lastName' => $data['spouse_last_name'],
                    'treeId' => $treeId,
                    'gender' => $data['spouse_gender'] ?? 'M',
                    'dateOfBirth' => $data['spouse_birth_date'] ?? null,
                    'alive' => $data['alive'] ?? 1,
                    'created_at' => $defaultDate,
                    'updated_at' => $defaultDate
                ];
                $spouseId = $this->memberModel->addMember($spouseData);
                if (!$spouseId) {
                    throw new Exception('Failed to create new spouse');
                }
                $spouseGender = $spouseData['gender'];
                break;

            default:
                throw new Exception('Invalid spouse type');
        }

        // Create family with spouse
        $familyData = [
            'tree_id' => $treeId,
            'husband_id' => $spouseGender === 'F' ? $memberId : $spouseId,
            'wife_id' => $spouseGender === 'F' ? $spouseId : $memberId,
            'marriage_date' => $data['marriage_date'] ?? null,
            'created_at' => $defaultDate,
            'updated_at' => $defaultDate
        ];

        $familyId = $this->familyModel->createFamily($familyData);
        if (!$familyId) {
            throw new Exception('Failed to create family');
        }

        return [
            'success' => true,
            'data' => [
                'family_id' => $familyId,
                'spouse_id' => $spouseId
            ]
        ];
    }

    private function addChild($data) {
        logapache("Adding child with data:");
        logapache($data);
        
        if (empty($data['tree_id']) || empty($data['member_id']) || empty($data['child_type'])) {
            throw new Exception('Missing required data: tree_id, member_id, or child_type');
        }

        $treeId = $data['tree_id'];
        $memberId = $data['member_id'];
        $defaultDate = date('Y-m-d H:i:s');

        // Handle child creation/selection
        $childId = null;
        if ($data['child_type'] === 'existing') {
            if (empty($data['child_id'])) {
                throw new Exception('Child ID required for existing child');
            }
            $childId = $data['child_id'];
            // Verify child exists
            $child = $this->memberModel->getMemberById($childId);
            if (!$child) {
                throw new Exception('Selected child not found');
            }
        } else {
            // Create new child
            if (empty($data['child_first_name']) || empty($data['child_last_name'])) {
                throw new Exception('First name and last name required for new child');
            }
            
            $childData = [
                'firstName' => $data['child_first_name'],
                'lastName' => $data['child_last_name'],
                'treeId' => $treeId,
                'gender' => $data['child_gender'] ?? 'M',
                'dateOfBirth' => $data['child_birth_date'] ?? null,
                'alive' => 1,
                'created_at' => $defaultDate,
                'updated_at' => $defaultDate
            ];
            
            $childId = $this->memberModel->addMember($childData);
            if (!$childId) {
                throw new Exception('Failed to create new child');
            }
        }

        // Handle family assignment
        $familyId = $data['family_id'] ?? 'new';
        if ($familyId === 'new') {
            $memberInfo = $this->memberModel->getMemberById($memberId);
            if (!$memberInfo) {
                throw new Exception('Member not found');
            }

            $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $memberInfo['gender'] === 'M' ? $memberId : null,
                'wife_id' => $memberInfo['gender'] === 'F' ? $memberId : null,
                'created_at' => $defaultDate,
                'updated_at' => $defaultDate
            ];
            
            $familyId = $this->familyModel->createFamily($familyData);
            if (!$familyId) {
                throw new Exception('Failed to create new family');
            }
        }

        // Link child to family
        $success = $this->familyModel->addChildToFamily($familyId, $childId, $treeId);
        if (!$success) {
            throw new Exception('Failed to link child to family');
        }

        return [
            'success' => true,
            'data' => [
                'child_id' => $childId,
                'family_id' => $familyId
            ]
        ];
    }

    private function addParent($data) {
        try {
            $defaultDate = date('Y-m-d H:i:s');
            $childId = $data['member_id'];
            $treeId = $data['tree_id'];

            // Create or fetch parent1
            $parent1Id = null;
            if ($data['parent1_type'] === 'existing') {
                if (empty($data['parent1_id'])) {
                    throw new Exception('Parent 1 ID required for existing parent');
                }
                $parent1Id = $data['parent1_id'];
                $parent1 = $this->memberModel->getMemberById($parent1Id);
                if (!$parent1) {
                    throw new Exception('Selected parent not found');
                }
                $parent1Gender = $parent1['gender'];
            } else {
                if (empty($data['parent1_first_name']) || empty($data['parent1_last_name'])) {
                    throw new Exception('Parent 1 first and last name are required');
                }
                $parent1Data = [
                    'firstName' => $data['parent1_first_name'],
                    'lastName' => $data['parent1_last_name'],
                    'treeId' => $treeId,
                    'dateOfBirth' => $data['parent1_birth_date'] ?? null,
                    'gender' => $data['parent1_gender'] ?? 'M',
                    'alive' => 1,
                    'created_at' => $defaultDate,
                    'updated_at' => $defaultDate
                ];
                $parent1Id = $this->memberModel->addMember($parent1Data);
                $parent1Gender = $parent1Data['gender'];
            }

            // Handle second parent if specified
            $parent2Id = null;
            if (!empty($data['second_parent_option']) && $data['second_parent_option'] !== 'none') {
                if ($data['second_parent_option'] === 'new') {
                    // Fix the field names by removing duplicates
                    $firstName = $data['parent2_first_name'] ?? null;
                    $lastName = $data['parent2_last_name'] ?? null;
                    
                    if (empty($firstName) || empty($lastName)) {
                        throw new Exception('Parent 2 first and last name are required');
                    }

                    $parent2Data = [
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'treeId' => $treeId,
                        'dateOfBirth' => $data['parent2_birth_date'] ?? null,
                        'gender' => $data['parent2_gender'] ?? 'F', // Default to opposite of parent1
                        'alive' => 1,
                        'created_at' => $defaultDate,
                        'updated_at' => $defaultDate
                    ];
                    $parent2Id = $this->memberModel->addMember($parent2Data);
                    $parent2Gender = $parent2Data['gender'];
                } else if ($data['second_parent_option'] === 'existing') {
                    if (empty($data['parent2_id'])) {
                        throw new Exception('Parent 2 ID required for existing parent');
                    }
                    $parent2Id = $data['parent2_id'];
                    $parent2 = $this->memberModel->getMemberById($parent2Id);
                    if (!$parent2) {
                        throw new Exception('Selected second parent not found');
                    }
                    $parent2Gender = $parent2['gender'];
                }
            }

            // Create family with appropriate husband/wife assignment
            $husband = null;
            $wife = null;
            
            // Determine husband/wife based on gender
            if ($parent2Id) {
                // Both parents exist
                if ($parent1Gender === 'M') {
                    $husband = $parent1Id;
                    $wife = $parent2Id;
                } else {
                    $husband = $parent2Id;
                    $wife = $parent1Id;
                }
            } else {
                // Single parent
                if ($parent1Gender === 'M') {
                    $husband = $parent1Id;
                } else {
                    $wife = $parent1Id;
                }
            }

            $familyData = [
                'tree_id' => $treeId,
                'husband_id' => $husband,
                'wife_id' => $wife,
                'created_at' => $defaultDate,
                'updated_at' => $defaultDate
            ];

            $familyId = $this->familyModel->createFamily($familyData);
            if (!$familyId) {
                throw new Exception('Failed to create family');
            }

            // Add child to family
            $success = $this->familyModel->addChildToFamily($familyId, $childId, $treeId);
            if (!$success) {
                throw new Exception('Failed to add child to family');
            }

            $this->sendResponse([
                'success' => true,
                'data' => [
                    'family_id' => $familyId,
                    'parent1_id' => $parent1Id,
                    'parent2_id' => $parent2Id
                ]
            ]);

        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function addOther($data) {
        $response = ['success' => false];

        // Validate required data
        if (!isset($data['member_id']) || !isset($data['other_type']) || !isset($data['relationship_type'])) {
            $response['message'] = 'Missing required data';
            echo json_encode($response);
            return;
        }

        $memberId = $data['member_id'];
        $treeId = $data['tree_id'];
        $otherId = null;

        // Handle other person based on type
        switch ($data['other_type']) {
            case 'existing':
                if (!isset($data['other_id'])) {
                    $response['message'] = 'Person ID required for existing person';
                    echo json_encode($response);
                    return;
                }
                $otherId = $data['other_id'];
                $otherPerson = $this->memberModel->getMemberById($otherId);
                if (!$otherPerson) {
                    $response['message'] = 'Selected person not found';
                    echo json_encode($response);
                    return;
                }
                break;

            case 'new':
                if (empty($data['other_first_name']) || empty($data['other_last_name'])) {
                    $response['message'] = 'First name and last name required for new person';
                    echo json_encode($response);
                    return;
                }
                $personData = [
                    'firstName' => $data['other_first_name'],
                    'lastName' => $data['other_last_name'],
                    'treeId' => $treeId,
                    'gender' => $data['other_gender'] ?? 'M',
                    'dateOfBirth' => $data['other_birth_date'] ?? null,
                    'alive' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $otherId = $this->memberModel->addMember($personData);
                if (!$otherId) {
                    $response['message'] = 'Failed to create new person';
                    echo json_encode($response);
                    return;
                }
                break;

            default:
                $response['message'] = 'Invalid person type';
                echo json_encode($response);
                return;
        }

        // Create relationship
        $relationshipId = $this->memberModel->addRelationship(
            $memberId,
            $otherId,
            $data['relationship_type'],
            $treeId
        );

        if (!$relationshipId) {
            $response['message'] = 'Failed to create relationship';
            echo json_encode($response);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'relationship_id' => $relationshipId,
                'other_id' => $otherId
            ]
        ]);
    }

    private function swapRelationship($data) {
        // Implementation for swapping relationships
    }

    private function updateRelationship($id, $data) {
        if (!$id) {
            throw new Exception('Relationship ID required');
        }

        $updateData = [
            'id' => $id,
            'relationship_type_id' => $data['relationship_type_id'],
            'relation_start' => $data['relation_start'] ?? null,
            'relation_end' => $data['relation_end'] ?? null
        ];

        $success = $this->memberModel->updateRelationship($updateData);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to update relationship');
        }
    }
}

// Initialize and handle the API request
$api = new IndividualsAPI($config);
$api->handleRequest();
