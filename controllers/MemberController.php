<?php
// require_once 'Member.php';

class MemberController extends AppController
{
    private $member;
    private $config;
    private $familyIdField = 'id'; // Class variable for family ID field

    public function __construct($config)
    {
        $db = $config['connection']??null;
        $this->config = $config;
        $this->member = new MemberModel($config);
        $this->basedir = dirname(__DIR__);
    }
    public function getMemberById($memberId)
    {
        $member = $this->member->getMemberById($memberId);
        return $member;
    }
    public function editMember($memberId)
    {
        $member = $this->member->getMemberById($memberId);
        $tagString = $this->member->getTagString($memberId);
        $relationships = $this->member->getMemberRelationships($memberId);
        $relationship_types = $this->member->getRelationshipTypes();
        
        // Add these lines to fetch family data
        $spouse_families = $this->member->getSpouseFamilies($memberId);
        $child_families = $this->member->getChildFamilies($memberId);
        
        if (!$member) {
            exit('Member not found.');
        }
        $treeId = $member['tree_id']??$_GET['tree_id'] ?? $_GET['tree_id']??0;

        $data = [
            "tagString" => $tagString,
            "member" => $member,
            "memberId"=> $member['id']??0,
            "treeId" => $member['tree_id'] ?? 0,
            "template" => "edit_member.tpl",
            "relationships" => $relationships,
            "relationship_types" => $relationship_types,
            // Add these lines to pass family data to template
            "spouse_families" => $spouse_families,
            "child_families" => $child_families,
            "section" => get_translation("Update Member"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => "",
            "tree_id" => $treeId,
            "translations"=>json_encode($this->config['translations'][$this->config['lang']]),
            "graph" => $this->config['graph']
        ];

        $data["menu"] = [
            [
                "link" => "index.php?action=export_tree&tree_id=$treeId",
                "text" => get_translation("Export Tree"),
            ],
            [
                "link" => "index.php?action=add_member&tree_id=$treeId",
                "text" => get_translation("New Member"),
            ],
            [
                "link" => "index.php?action=edit_tree&tree_id=$treeId",
                "text" => get_translation("List Members"),
            ],
            [
                "link" => "index.php?action=list_trees",
                "text" =>  get_translation("Trees"),
            ]
        
        ];        

        // Fetch member relationships

        //apachelog("++++++++++++++++" . $_SERVER['REQUEST_METHOD']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //apachelog("--------------");
            $updatedMember = [
                'memberId' => $memberId,
                'firstName' => $_POST['first_name'],
                'lastName' => $_POST['last_name'],
                'dateOfBirth' => $_POST['birth_date'],
                'placeOfBirth' => $_POST['birth_place'],
                'dateOfDeath' => $_POST['death_date'],
                'placeOfDeath' => $_POST['death_place'],
                'gender' => $_POST['gender'] ?? null,  // Ensure gender is set
                'source' => $_POST['source'],
            ];
            $updatedMember['alive'] = isset($_POST['alive']) ? 1 : 0;  
            ///apachelog("++++++++++++++++");
            //apachelog($updatedMember);
            // Handle member update logic
            if ($updatedMember['gender'] === null) {
                $data["error"] = "Gender is required.";
            } else {
                $success = $this->member->updateMember($updatedMember);
                if ($success) {
                    header("Location: index.php?action=edit_member&member_id=$memberId");
                    exit();
                } else {
                    $data["error"] = "Failed to update member.";
                }
            }
        }

        echo $this->render_master($data);
        //include $this->basedir . "/templates/edit_member.php";
    }
    public function addMember($treeId)
    {
        $data = [
            "template" => "add_member.tpl",
            "section" => get_translation("Add Member"),
            "tree_description" => get_translation("Description"),
            "go_back" => get_translation("Back to List"),            
            "error" => "",
            "tree_id" => $_GET['tree_id'] ?? $_GET['tree_id'],
            "graph" => $this->config['graph']
        ];

        $treeId = $_GET['tree_id'] ?? $_GET['tree_id']; // Get tree_id from the request
        $data["menu"] = [
            [
                "link" => "index.php?action=export_tree&tree_id=$treeId",
                "text" => get_translation("Export Tree"),
            ],

            [
                "link" => "index.php?action=add_member&tree_id=$treeId",
                "text" => get_translation("New Member"),
            ],
            [
                "link" => "index.php?action=edit_tree&tree_id=$treeId",
                "text" => get_translation("List Members"),
            ],
            [
                "link" => "index.php?action=list_trees",
                "text" =>  get_translation("Trees"),
            ]
        
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $dateOfBirth = $_POST['birth_date'];
            $placeOfBirth = $_POST['birth_place'];
            $gender = $_POST['gender'];  // Changed from gender_id to gender
            $new_member = [
                'treeId' => $treeId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'dateOfBirth' => $dateOfBirth,
                'placeOfBirth' => $placeOfBirth,
                'gender' => $gender,
                'dateOfDeath' => null,
            ];
            //$treeId, $firstName, $lastName, $dateOfBirth, $placeOfBirth, $genderId
            $success = $this->member->addMember($new_member);
            if ($success) {
                header("Location: index.php?action=list_members&tree_id=$treeId");
                exit();
            } else {
                $error = "Failed to add member.";
            }
        }
        echo $this->render_master($data);

        //include $this->basedir . "/templates/add_member.php";
    }
    public function getRelationshipTypes($treeId)
    {
        $results = $this->member->getRelationshipTypes($treeId);
        echo json_encode($results);
    }

    public function autocompleteMember($termId, $memberId, $treeId)
    {
        $results = $this->member->autocompleteMember($termId,  $memberId  , $treeId);
        //apachelog("--autocompleteMember treeId $treeId termId $termId member_id $memberId");
        echo json_encode($results);
    }
    public function autocompleteMemberGet() {
        if (!isset($_GET['term']) || !isset($_GET['member_id']) || !isset($_GET['tree_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Term, member ID, and tree ID are required']);
            exit;
        }
    
        $term = $_GET['term'];
        $memberId = $_GET['member_id'];
        $treeId = $_GET['tree_id'];
    
        try {
            // Fetch matching members from the model
            $members = $this->memberModel->autocompleteMembers($term, $memberId, $treeId);
            
            header('Content-Type: application/json');
            echo json_encode($members);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    public function getRelationships($memberId)
    {
        $relationships = $this->member->getMemberRelationships($memberId); // Fetch relationships
        echo json_encode($relationships); // Output relationships as JSON (for AJAX handling)
    }
    public function listTags() {
        $treeId = $_POST['tree_id'];
        $memberId = $_POST['member_id'];
        $tagList = $this->member->listTags($treeId, $memberId);

        echo json_encode(['success'=>true,'tags'=>$tagList]);
    }
    public function addTag() {
        $newTag = [
            'tree_id'=>$_POST['tree_id'],
            'member_id'=> $_POST['member_id'],
            'tag'=> $_POST['tag']
        ];
        $success = $this->member->addTag($newTag);
        if ($success) {
            return json_encode(['status'=>'success']);
        } else {
            return json_encode(['status'=>'fail']);
        }
    }
    public function deleteTag() {
        $delTag = [
            'tree_id'=>$_POST['tree_id'],
            'member_id'=> $_POST['member_id'],
            'tag'=> $_POST['tag']
        ];
        $success = $this->member->deleteTag($delTag);
        if ($success) {
            return json_encode(['status'=>'success']);
        } else {
            return json_encode(['status'=>'fail']);
        }
        exit;
    }
    public function addRelationship($memberId, $personId2, $relationshipTypeId) 
    {
        try {
            // Get tree_id and current member's info
            $member = $this->member->getMemberById($memberId);
            if (!$member) {
                throw new Exception("Invalid member ID");
            }
            $treeId = $member['tree_id'];

            // Handle new person creation
            if (empty($personId2) && isset($_POST['member_type']) && $_POST['member_type'] === 'new') {
                $newPersonData = [
                    'treeId' => $treeId,
                    'firstName' => $_POST['new_first_name'],
                    'lastName' => $_POST['new_last_name'],
                    'gender' => $_POST['new_gender'],
                    'dateOfBirth' => $_POST['new_birth_date'] ?? null,
                    'placeOfBirth' => $_POST['new_birth_place'] ?? null,
                    'alive' => 1
                ];
                
                $personId2 = $this->member->addMember($newPersonData);
                if (!$personId2) {
                    throw new Exception("Failed to create new person");
                }
            }

            // Handle different relationship types
            if ($relationshipTypeId == 1) { // Spouse relationship
                $currentMemberGender = $member['gender'];
                
                $familyData = [
                    'tree_id' => $treeId,
                    'husband_id' => $currentMemberGender === 'M' ? $memberId : $personId2,
                    'wife_id' => $currentMemberGender === 'F' ? $memberId : $personId2,
                    'marriage_date' => $_POST['marriage_date'] ?? null
                ];

                $success = $this->member->createFamily($familyData);
                if (!$success) {
                    throw new Exception("Failed to create family");
                }
            } else {
                // Handle other relationship types here
                // For example: parent-child, sibling, etc.
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            error_log("Error in addRelationship: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function updateRelationship($postData)
    {
        // Example: Assuming $_POST contains 'relationship_id', 'member_id', 'member2_id', 'tree_id', and 'relationship_type'
        $relationshipId = isset($postData['relationship_id']) ? $postData['relationship_id'] : null;
        $personId1 = isset($postData['member_id']) ? $postData['member_id'] : null;
        $personId2 = isset($postData['member2_id']) ? $postData['member2_id'] : null;
        $familyTreeId = isset($postData['tree_id']) ? $postData['tree_id'] : null;
        $relationStart = isset($postData['relation_start']) ? $postData['relation_start'] : null;
        $relationEnd = isset($postData['relation_start']) ? $postData['relation_end'] : null;
        $relationshipType = isset($postData['relationship_type']) ? $postData['relationship_type'] : null;
        //apachelog("--- id $relationshipId p1 $personId1 t $familyTreeId  r $relationshipType");
        if (!$relationshipId || !$personId1 || !$relationshipType) {
            return json_encode(['success' => false, 'message' => 'Missing required parameters']);
        }

        $relation = [
            'relationshipId' => $relationshipId,
            'personId1' => $personId1,
            'personId2' => $personId2,
            'relationStart' => $relationStart,
            'relationEnd' => $relationEnd,
            'relationshipTypeId' => $relationshipType,
        ];
        //$relationshipId, $personId1, $relationshipType
        $this->member->updateMemberRelationship($relation);

        // Example: Update relationship in database or storage
        // Example: Replace with actual implementation

        // Simulated response
        $response = ['success' => true, 'message' => 'Relationship updated successfully'];

        // Return JSON response
        return json_encode($response);
    }
    public function swapRelationshipAction()
    {
        $relationshipId = $_POST['relationship_id']; // Or get it from the request in another way

        try {
            $result = $this->member->swapRelationship($relationshipId);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Relationship not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function deleteMember($memberId)
    {
        // Implement logic to delete a member from the database or data source
        // Example:

        $success = $this->member->deleteMember($memberId);
        return $success;
    }
    public function deleteRelationship($relationshipId)
    {
        $success = $this->member->deleteRelationship($relationshipId);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete relationship.']);
        }
        exit();
    }
    public function getFamilies($memberId = null)
    {
        if (!$memberId) {
            $memberId = $_GET['member_id'] ?? null;
        }
        
        if (!$memberId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No member ID provided']);
            exit;
        }

        $spouse_families = $this->member->getSpouseFamilies($memberId);
        $child_families = $this->member->getChildFamilies($memberId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'spouse_families' => $spouse_families,
            'child_families' => $child_families
        ]);
        exit;
    }
    public function getSpouseFamilies() {
        if (!isset($_GET['member_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Member ID is required']);
            exit;
        }
    
        $memberId = $_GET['member_id'];
        
        try {
            // Fetch spouse families from the model
            $families = $this->member->getSpouseFamilies($memberId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'spouse_families' => $families
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    public function deleteFamilyMember()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('Delete family member request: ' . print_r($_POST, true));
            
            $familyId = $_POST['family_id'] ?? null;
            $childId = $_POST['child_id'] ?? null;
            $spouseId = $_POST['spouse_id'] ?? null;
            $deleteType = $_POST['delete_type'] ?? null;

            if (!$familyId) {
                throw new Exception('Family ID is required');
            }

            if ($childId) {
                // Handle child deletion
                if ($deleteType === 'remove') {
                    $success = $this->member->removeChildFromFamily($childId, $familyId);
                } else {
                    $success = $this->member->deleteMember($childId);
                }
            } else {
                // Handle spouse deletion - even if spouseId is null
                switch ($deleteType) {
                    case '1': // Remove relationship only
                        $success = $this->member->removeSpouseFromFamily($spouseId, $familyId);
                        break;
                    case '2': // Delete spouse, keep children
                        $success = $spouseId ? 
                            $this->member->deleteSpouseKeepChildren($spouseId, $familyId) :
                            $this->member->removeSpouseFromFamily($spouseId, $familyId);
                        break;
                    case '3': // Delete spouse and children
                        $success = $spouseId ? 
                            $this->member->deleteSpouseAndChildren($spouseId, $familyId) :
                            $this->member->deleteFamilyAndChildren($familyId);
                        break;
                    default:
                        throw new Exception('Invalid delete type');
                }
            }

            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            error_log('Error in deleteFamilyMember: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    public function replaceSpouse()
    {
        header('Content-Type: application/json');
        
        try {
            error_log('Replace spouse request: ' . print_r($_POST, true));
            
            $familyId = $_POST['family_id'] ?? null;
            $spouseType = $_POST['spouse_type'] ?? null;
            $memberGender = $_POST['member_gender'] ?? null;
            $spouseId = $_POST['spouse_id'] ?? null;
            $treeId = $_POST['tree_id'] ?? null;

            error_log("Processing replace spouse - Family: $familyId, Spouse: $spouseId, Gender: $memberGender, Tree: $treeId");

            if (!$familyId || !$spouseType || !$memberGender || !$treeId) {
                throw new Exception('Missing required parameters: ' . 
                                  (!$familyId ? 'family_id ' : '') .
                                  (!$spouseType ? 'spouse_type ' : '') .
                                  (!$memberGender ? 'member_gender ' : '') .
                                  (!$treeId ? 'tree_id' : ''));
            }

            if ($spouseType === 'existing') {
                if (!$spouseId) {
                    throw new Exception('No spouse selected');
                }
            } else {
                // Create new spouse
                if (empty($_POST['new_first_name']) || empty($_POST['new_last_name'])) {
                    throw new Exception('First name and last name are required for new spouse');
                }
                
                $new_member = [
                    'treeId' => $treeId,
                    'firstName' => $_POST['new_first_name'],
                    'lastName' => $_POST['new_last_name'],
                    'dateOfBirth' => $_POST['new_birth_date'] ?? null,
                    'gender' => $memberGender == 'M' ? 'F' : 'M',  // Changed from gender_id to gender
                ];
                $spouseId = $this->member->addMember($new_member);
                if (!$spouseId) {
                    throw new Exception('Failed to create new spouse');
                }
            }

            // Update the family
            $success = $this->member->updateFamilySpouse(
                $familyId, 
                $spouseId, 
                $memberGender, 
                $_POST['marriage_date'] ?? null
            );

            if (!$success) {
                throw new Exception('Failed to update family with new spouse');
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Error in replaceSpouse: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function visualizeDescendants($memberId) {
        $member = $this->member->getMemberById($memberId);
        if (!$member) {
            exit('Member not found.');
        }

        $data = [
            "member" => $member,
            "template" => "visualize_descendants.tpl",
            "section" => get_translation("Visualize Descendants"),
            "memberId" => $memberId,
            "treeId" => $member['tree_id'],
        ];

        echo $this->render_master($data);
    }

    public function getDescendantsData($memberId) {
        header('Content-Type: application/json');
        $descendants = $this->member->getDescendantsHierarchy($memberId);
        echo json_encode($descendants);
        exit;
    }

    public function getDescendantsHierarchy($memberId) {
        $person = $this->getMemberById($memberId);
        if (!$person) {
            return null;
        }

        // Get all families where this person is a spouse
        $spouseFamilies = $this->getSpouseFamilies($memberId);
        
        $result = [
            'id' => $person['id'],
            'name' => trim($person['first_name'] . ' ' . $person['last_name']),
            'data' => [
                'birth' => $person['birth_date'],
                'death' => $person['death_date'],
                'gender' => $person['gender']
            ],
            'marriages' => []
        ];

        // Process each family
        foreach ($spouseFamilies as $family) {
            $spouseId = ($person['gender'] == 'M') ? $family['wife_id'] : $family['husband_id'];
            $spouseName = ($person['gender'] == 'M') ? $family['wife_name'] : $family['husband_name'];
            
            // Add spouse information if exists
            $marriage = [
                'id' => $family['family_id'],
                'spouse' => $spouseId ? [
                    'id' => $spouseId,
                    'name' => $spouseName,
                    'data' => [
                        'gender' => ($person['gender'] == 'M') ? 'F' : 'M', // Set opposite gender
                        'birth' => null,  // Add if available
                        'death' => null   // Add if available
                    ]
                ] : null,
                'children' => []
            ];

            // Add children for this marriage
            if (isset($family['children'])) {
                foreach ($family['children'] as $child) {
                    $childDescendants = $this->getDescendantsHierarchy($child['id']);
                    if ($childDescendants) {
                        $marriage['children'][] = $childDescendants;
                    }
                }
            }
            
            $result['marriages'][] = $marriage;
        }

        return $result;
    }
}
